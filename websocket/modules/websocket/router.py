from fastapi import APIRouter, BackgroundTasks, WebSocket, WebSocketDisconnect

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
        background_tasks.add_task(keep_alive, websocket)

        while True:
            try:
                data = await websocket.receive_text()
                await websocket.send_text(data)
            except WebSocketDisconnect:
                break
    except Exception as e:
        print(f"Unexpected error: {e}")
    finally:
        # Disconnect user after the connection is closed
        user_id = next(
            (
                uid
                for uid, ws in manager.active_connections.items()
                if ws == websocket
            ),
            None,
        )
        if user_id is not None:
            await manager.disconnect(user_id)
