from fastapi import FastAPI, Query
import uvicorn
import os
from p import check_card  # <- from your custom p.py script

app = FastAPI()

@app.get("/")
def home():
    return {"status": "✅ Braintree Checker API Online", "usage": "/check?cc=xxxx|mm|yy|cvv"}

@app.get("/check")
def check(cc: str = Query(..., description="Card: xxxx|mm|yy|cvv")):
    try:
        result = check_card(cc)
        return {
            "cc": cc,
            "status": result.get("status", "Unknown"),
            "message": result.get("message", ""),
            "source": "Made by @IPxKlNGYT"
        }
    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 10000))
    uvicorn.run("main:app", host="0.0.0.0", port=port)
