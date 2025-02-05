from fastapi import FastAPI
from fastapi.responses import JSONResponse
from modules.schemas import NotificationAllRequest, NotificationRequest
from modules.websocket.router import manager, ws

app = FastAPI()

# Bind the WebSocket router to the app
app.include_router(ws)


@app.get("/")
async def root():
    return {"message": "Hello, I'm a WebSocket server!"}


@app.post("/send-notification/")
async def send_notification(request: NotificationRequest):
    try:
        print(f"\n\nSending notification to user: {request.user_id}")

        data = {
            "user_id": request.user_id,
            "event": request.event,
            "entity_id": request.entity_id,
            "entity_type": request.entity_type,
            "title": request.title,
            "body": request.body,
            "event_data": request.event_data,
        }

        print(f"Data: {data}")

        await manager.send_message(data)
        return JSONResponse(
            content={"message": "Notification sent to user"}, status_code=200
        )
    except Exception as e:
        return JSONResponse(content={"message": str(e)}, status_code=400)


@app.post("/broadcast-notification/")
async def send_notification_all(request: NotificationAllRequest):
    try:

        data = {
            "event": request.event,
            "title": request.title,
            "body": request.body,
        }

        await manager.broadcast(data)
        return JSONResponse(
            content={"message": "Notification sent to all users"},
            status_code=200,
        )
    except Exception as e:
        return JSONResponse(content={"message": str(e)}, status_code=400)
