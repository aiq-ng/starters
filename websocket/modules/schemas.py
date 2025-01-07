from typing import Union

from pydantic import BaseModel


class NotificationRequest(BaseModel):
    user_id: str
    content: Union[str, dict, list]


class NotificationAllRequest(BaseModel):
    content: Union[str, dict, list]
