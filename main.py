from fastapi import FastAPI, Query
from p import check_card  # Assuming check_card(cc) is defined in p.py

app = FastAPI()

@app.get("/")
def home():
    return {"message": "Cloudways/Braintree CC API Live!"}

@app.get("/check")
def check(cc: str = Query(..., description="Card format: xxxx|mm|yy|cvv")):
    result = check_card(cc)
    return {"result": result}
