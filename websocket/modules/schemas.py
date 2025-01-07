from pydantic import BaseModel


class NotificationRequest(BaseModel):
    user_id: str
    event: str
    entity_id: str
    entity_type: str
    title: str
    body: str


class NotificationAllRequest(BaseModel):
    event: str
    title: str
    body: str
