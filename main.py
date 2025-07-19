from fastapi import FastAPI
import uvicorn

app = FastAPI()

@app.get("/")
def home():
    return {"status": "API is working"}

# Replace below with your real endpoint
@app.get("/check")
def checker(cc: str):
    return {"cc": cc, "status": "Approved ✅"}  # dummy

if __name__ == "__main__":
    import os
    port = int(os.environ.get("PORT", 10000))
    uvicorn.run("main:app", host="0.0.0.0", port=port, reload=False)
