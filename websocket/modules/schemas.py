from typing import Any, Optional

from pydantic import BaseModel


class NotificationRequest(BaseModel):
    user_id: str
    event: str
    entity_id: Optional[str] = None
    entity_type: Optional[str] = None
    title: Optional[str] = None
    body: Optional[str] = None
    event_data: Optional[Any] = None


class NotificationAllRequest(BaseModel):
    event: str
    title: str
    body: str
