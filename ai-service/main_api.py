from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import tensorflow as tf
from tensorflow.keras.preprocessing.sequence import pad_sequences
import numpy as np
import pickle
# import google.generativeai as genai
import os

# 1. PERSIAPAN MODEL AI & TOKENIZER

# Kita harus mendefinisikan ulang Custom Layer agar model bisa di-load
class SimpleAttention(tf.keras.layers.Layer):
    def __init__(self, **kwargs):
        super(SimpleAttention, self).__init__(**kwargs)
    def build(self, input_shape):
        self.W = self.add_weight(name='attention_weight', shape=(input_shape[-1], 1), initializer='random_normal', trainable=True)
        super(SimpleAttention, self).build(input_shape)
    def call(self, x):
        scores = tf.matmul(x, self.W) 
        scores = tf.squeeze(scores, axis=-1) 
        weights = tf.nn.softmax(scores, axis=-1) 
        weights = tf.expand_dims(weights, axis=-1) 
        context_vector = tf.reduce_sum(x * weights, axis=1) 
        return context_vector

# Load Model dan Tokenizer
# compile=False digunakan karena kita hanya butuh model untuk inference (prediksi), bukan training
model = tf.keras.models.load_model("economic_sentiment_model.keras", custom_objects={"SimpleAttention": SimpleAttention}, compile=False)

with open('tokenizer.pkl', 'rb') as handle:
    tokenizer = pickle.load(handle)

MAX_LENGTH = 50


# 2. PERSIAPAN GENERATIVE AI (SIDE QUEST)

# Masukkan API Key Gemini kamu di sini (dapatkan gratis di Google AI Studio)
# Di environment production, gunakan os.getenv("GEMINI_API_KEY")
#GEMINI_API_KEY = "AIzaSyB6C4okKDFnZy1RJuy2EPDnzwErP8kwF8Y" 
# genai.configure(api_key=GEMINI_API_KEY)
# gen_model = genai.GenerativeModel('gemini-1.5-flash')


# 3. INISIALISASI FASTAPI

app = FastAPI(title="EcoNomic AI Backend", description="API untuk Analisis Sentimen dan Rangkuman Berita Ekonomi")

# Skema data (JSON) yang diterima dari Frontend/Backend Laravel
class NewsRequest(BaseModel):
    teks_berita: str

# 1: Prediksi Sentimen Standar
@app.post("/predict")
def predict_sentiment(request: NewsRequest):
    try:
        sequence = tokenizer.texts_to_sequences([request.teks_berita])
        padded_sequence = pad_sequences(sequence, maxlen=MAX_LENGTH, padding='post', truncating='post')
        
        prediction = model.predict(padded_sequence, verbose=0)
        class_idx = int(np.argmax(prediction[0]))
        confidence = float(np.max(prediction[0]))
        
        labels = {0: "Negatif", 1: "Netral", 2: "Positif"}
        
        return {
            "status": "success",
            "teks_asli": request.teks_berita,
            "sentimen": labels[class_idx],
            "confidence_score": round(confidence * 100, 2)
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# 2: Fitur Sekunder (GenAI Summary + Prediksi)
@app.post("/analyze-and-summarize")
def analyze_and_summarize(request: NewsRequest):
    try:
        # 1. Dapatkan sentimen dari model Deep Learning kita
        sequence = tokenizer.texts_to_sequences([request.teks_berita])
        padded_sequence = pad_sequences(sequence, maxlen=MAX_LENGTH, padding='post', truncating='post')
        prediction = model.predict(padded_sequence, verbose=0)
        class_idx = int(np.argmax(prediction[0]))
        labels = {0: "Negatif", 1: "Netral", 2: "Positif"}
        sentimen_hasil = labels[class_idx]
        
        # 2. Dapatkan rangkuman dari Google Gemini AI
        prompt = f"Rangkum berita ekonomi berikut menjadi satu kalimat singkat yang mudah dipahami investor retail: '{request.teks_berita}'"
        genai_response = gen_model.generate_content(prompt)
        rangkuman = genai_response.text.strip()
        
        return {
            "status": "success",
            "sentimen": sentimen_hasil,
            "rangkuman_ai": rangkuman,
            "pesan": "Berhasil dianalisis oleh Deep Learning & Gemini AI"
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
