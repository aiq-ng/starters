from fastapi import WebSocket
from starlette.websockets import WebSocketState

from modules.auth import decode_token


class ConnectionManager:
    def __init__(self):
        self.active_connections: dict[int, WebSocket] = {}

    async def authorize(self, websocket: WebSocket):
        """Authorize User Connection"""

        token = websocket.query_params.get("token")

        print(f"\nToken: {token}")

        if not token:
            await websocket.close(code=1008, reason="Missing access token")
            return None

        try:
            payload = await decode_token(token)
            user_id = payload.get("data", {}).get("id")

            print(f"\nPayload: {payload}")

            if not user_id:
                raise ValueError("User ID not found in token payload")

            print(f"\nUser ID: {user_id}\n")
            return user_id

        except Exception as e:
            await websocket.close(code=1008, reason=f"Invalid token: {e}")
            return None

    async def connect(self, websocket: WebSocket):
        """Accept WebSocket connection after authorization"""
        user_id = await self.authorize(websocket)

        if user_id is None:
            return

        await websocket.accept()
        self.active_connections[user_id] = websocket

    async def disconnect(self, user_id: int):
        """Disconnect user and remove from active connections"""
        websocket = self.active_connections.get(user_id)
        if websocket:
            await websocket.close()
            del self.active_connections[user_id]

    async def send_message(self, user_id: int, message: str):
        """Send a message to a specific user"""
        websocket = self.active_connections.get(user_id)
        if websocket:
            if websocket.client_state == WebSocketState.CONNECTED:
                await websocket.send_text(message)

    async def broadcast(self, message: str):
        for websocket in self.active_connections.values():
            await websocket.send_text(message)


manager = ConnectionManager()
