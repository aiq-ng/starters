#!/bin/bash

echo "Starting the application..."
exec uvicorn main:app --host 0.0.0.0 --port "$PORT"
