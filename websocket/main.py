from fastapi import FastAPI
from fastapi.responses import JSONResponse

from modules.schemas import NotificationAllRequest, NotificationRequest
from modules.websocket.router import manager, ws

app = FastAPI()

# Bind the WebSocket router to the app
app.include_router(ws)


@app.post("/send-notification/")
async def send_notification(request: NotificationRequest):
    try:
        print(f"\n\nSending notification to user: {request.user_id}")

        await manager.send_message(request.user_id, request.content)
        return JSONResponse(
            content={"message": "Notification sent to user"}, status_code=200
        )
    except Exception as e:
        return JSONResponse(content={"message": str(e)}, status_code=400)


@app.post("/broadcast-notification/")
async def send_notification_all(request: NotificationAllRequest):
    try:
        await manager.broadcast(request.content)
        return JSONResponse(
            content={"message": "Notification sent to all users"},
            status_code=200,
        )
    except Exception as e:
        return JSONResponse(content={"message": str(e)}, status_code=400)
