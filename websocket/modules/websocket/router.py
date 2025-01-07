from fastapi import APIRouter, BackgroundTasks, WebSocket, WebSocketDisconnect
from starlette.websockets import WebSocketState

from modules.utils import keep_alive
from modules.websocket.manager import ConnectionManager

ws = APIRouter(prefix="/ws", tags=["WebSocket"])

manager = ConnectionManager()


@ws.websocket("/")
async def connect_websocket(
    websocket: WebSocket, background_tasks: BackgroundTasks
):
    """Handle WebSocket events"""

    try:
        await manager.connect(websocket)

        # Background task for keep-alive
        background_tasks.add_task(keep_alive, websocket)

        # Handling incoming messages
        while True:
            try:
                data = await websocket.receive_text()
                await websocket.send_text(f"Message received: {data}")
            except WebSocketDisconnect:
                break

    except Exception as e:
        # Handle connection errors and ensure proper closing of WebSocket
        if websocket.client_state != WebSocketState.CLOSED:
            await websocket.close(code=1008, reason=f"Connection error: {e}")
