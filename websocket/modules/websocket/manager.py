import json

from fastapi import APIRouter, WebSocket
from starlette.websockets import WebSocketState

from modules.auth import decode_token

ws = APIRouter(prefix="/ws", tags=["WebSocket"])


class ConnectionManager:
    def __init__(self):
        self.active_connections: dict[int, WebSocket] = {}

    async def authorize(self, websocket: WebSocket):
        """Authorize User Connection"""
        try:
            token = websocket.query_params.get("token")

            if not token:
                await websocket.close(code=1008, reason="Missing access token")
                return None

            payload = await decode_token(token)
            if not payload:
                await websocket.close(code=1008, reason="Invalid access token")
                return None

            user_id = payload.get("data", {}).get("id")
            if not user_id:
                raise ValueError("User ID not found in token payload")

            return user_id
        except Exception as e:
            await websocket.close(code=1008, reason=f"Invalid token: {e}")
            return None

    async def connect(self, websocket: WebSocket):
        """Accept WebSocket connection after authorization"""
        try:
            user_id = await self.authorize(websocket)
            if user_id is None:
                return

            await websocket.accept()
            self.active_connections[user_id] = websocket
        except Exception as e:
            # Log the exception if needed
            print(f"Error during connection: {e}")
            if websocket.client_state != WebSocketState.CLOSED:
                await websocket.close(
                    code=1008, reason=f"Connection error: {e}"
                )

    async def disconnect(self, user_id: int):
        """Disconnect user and remove from active connections"""
        try:
            websocket = self.active_connections.pop(user_id, None)
            if (
                websocket
                and websocket.client_state == WebSocketState.CONNECTED
            ):
                await websocket.close()
        except Exception as e:
            # Log the exception if needed
            print(f"Error during disconnect: {e}")

    async def send_message(self, data: dict):
        """Send a message to a specific user"""
        try:
            websocket = self.active_connections.get(data.get("user_id"))
            if (
                websocket
                and websocket.client_state == WebSocketState.CONNECTED
            ):
                await websocket.send_text(json.dumps(data))
        except Exception as e:
            # Log the exception if needed
            print(f"Error sending message: {e}")

    async def broadcast(self, data: dict):
        """Broadcast a message to all connected users"""
        try:
            for websocket in self.active_connections.values():
                if websocket.client_state == WebSocketState.CONNECTED:
                    await websocket.send_text(json.dumps(data))
        except Exception as e:
            # Log the exception if needed
            print(f"Error broadcasting message: {e}")


manager = ConnectionManager()
