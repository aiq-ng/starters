import json

from fastapi import WebSocket
from modules.auth import decode_token
from modules.logging import logger
from starlette.websockets import WebSocketState


class ConnectionManager:
    def __init__(self):
        self.active_connections: dict[int, list[WebSocket]] = {}

    async def authorize(self, websocket: WebSocket):
        """Authorize User Connection"""
        try:
            token = websocket.query_params.get("token")

            if not token:
                logger.error("Missing access token")
                await websocket.close(code=1008, reason="Missing access token")
                return None

            payload = await decode_token(token)
            if not payload:
                logger.error("Invalid access token")
                await websocket.close(code=1008, reason="Invalid access token")
                return None

            user_id = payload.get("data", {}).get("id")

            if not user_id:
                logger.error("User ID not found in token payload")
                raise ValueError("User ID not found in token payload")

            return user_id
        except Exception as e:
            logger.error(f"Invalid token: {e}")
            await websocket.close(code=1008, reason=f"Invalid token: {e}")
            return None

    async def connect(self, websocket: WebSocket):
        """
        Accept WebSocket connection after authorization,
        with a max of 5 connections per user
        """

        try:
            user_id = await self.authorize(websocket)
            if user_id is None:
                return

            # Check if the user already has 5 connections
            if user_id in self.active_connections:
                # If the user has 5 connections, replace the first (oldest) one
                if len(self.active_connections[user_id]) >= 5:
                    oldest_websocket = self.active_connections[user_id].pop(0)
                    if (
                        oldest_websocket.client_state
                        == WebSocketState.CONNECTED
                    ):
                        await oldest_websocket.close(
                            code=1000, reason="Replacing connection"
                        )

            await websocket.accept()
            if user_id not in self.active_connections:
                self.active_connections[user_id] = []
            self.active_connections[user_id].append(websocket)

        except Exception as e:
            logger.error(f"Error during connection: {e}")
            if websocket.client_state != WebSocketState.CLOSED:
                await websocket.close(
                    code=1008, reason=f"Connection error: {e}"
                )

    async def disconnect(self, user_id: int, websocket: WebSocket):
        """Disconnect user and remove from active connections"""
        try:
            if user_id in self.active_connections:
                self.active_connections[user_id].remove(websocket)
                if not self.active_connections[user_id]:
                    del self.active_connections[user_id]

                if websocket.client_state == WebSocketState.CONNECTED:
                    await websocket.close()
        except Exception as e:
            logger.error(f"Error during disconnect: {e}")

    async def send_message(self, data: dict):
        """Send a message to a specific user"""
        try:
            user_id = data.get("user_id")
            if user_id in self.active_connections:
                for websocket in self.active_connections[user_id]:
                    if websocket.client_state == WebSocketState.CONNECTED:
                        await websocket.send_text(json.dumps(data))
        except Exception as e:
            logger.error(f"Error sending message: {e}")

    async def broadcast(self, data: dict):
        """Broadcast a message to all connected users"""
        try:
            for websockets in self.active_connections.values():
                for websocket in websockets:
                    if websocket.client_state == WebSocketState.CONNECTED:
                        await websocket.send_text(json.dumps(data))
        except Exception as e:
            logger.error(f"Error broadcasting message: {e}")


manager = ConnectionManager()
