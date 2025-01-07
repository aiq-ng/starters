from pydantic import BaseModel


class NotificationRequest(BaseModel):
    user_id: str
    event: str
    title: str
    body: str


class NotificationAllRequest(BaseModel):
    event: str
    title: str
    body: str
