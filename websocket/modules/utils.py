import asyncio

from fastapi import WebSocket


async def keep_alive(websocket: WebSocket, interval: int = 30):
    """
    Send keep-alive pings to the WebSocket client every `interval` seconds.
    """
    while True:
        await asyncio.sleep(interval)
        try:
            await websocket.send_text("ping")
        except Exception as e:
            print(f"Error sending keep-alive ping: {e}")
            break
