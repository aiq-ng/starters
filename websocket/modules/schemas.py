from typing import Optional

from pydantic import BaseModel


class NotificationRequest(BaseModel):
    user_id: str
    event: str
    entity_id: Optional[str] = None
    entity_type: Optional[str] = None
    title: str
    body: str
    event_data: Optional[dict] = []


class NotificationAllRequest(BaseModel):
    event: str
    title: str
    body: str
