import sys
import json
import joblib
import pandas as pd
from datetime import datetime
import os

# --- Fungsi kategorisasi suhu (HARUS SAMA PERSIS DENGAN SAAT PELATIHAN) ---
def categorize_temperature(temp):
    temp = float(temp)
    if temp >= 22 and temp <= 25:
        return 'Sangat Nyaman'
    elif (temp >= 20 and temp < 22) or (temp > 25 and temp <= 28):
        return 'Kurang Nyaman'
    else:
        return 'Tidak Nyaman'

MODEL_DIR = '../models/' 
MODEL_PATH = os.path.join(MODEL_DIR, 'naive_bayes_model.pkl')
LABEL_ENCODER_PATH = os.path.join(MODEL_DIR, 'label_encoder.pkl')
ONE_HOT_ENCODER_PATH = os.path.join(MODEL_DIR, 'one_hot_encoder.pkl')

# --- Muat model dan encoders ---
try:
    model = joblib.load(MODEL_PATH)
    le_label = joblib.load(LABEL_ENCODER_PATH)
    ohe_features = joblib.load(ONE_HOT_ENCODER_PATH)
except FileNotFoundError as e:
    # Cetak pesan error dalam format JSON agar PHP bisa menangkapnya
    print(json.dumps({"error": f"Model file not found: {e}. Make sure you've run train_naive_bayes.py and paths are correct."}))
    sys.exit(1) # Keluar dengan kode error

# --- Ambil data suhu dari argumen command line PHP ---
# PHP akan mengirimkan suhu_terakhir sebagai argumen pertama
if len(sys.argv) < 2:
    print(json.dumps({"error": "No temperature provided. Usage: python predict_temp_condition.py <temperature>"}));
    sys.exit(1)

try:
    live_temp = float(sys.argv[1])
except ValueError:
    print(json.dumps({"error": "Invalid temperature value provided. Must be a number."}));
    sys.exit(1)

live_time = datetime.now() # Waktu saat ini untuk fitur jam dan hari_minggu

# --- Pra-pemrosesan data live (HARUS SAMA dengan pra-pemrosesan data pelatihan) ---
live_suhu_cat = categorize_temperature(live_temp)
live_jam = live_time.hour
live_hari_minggu = live_time.weekday() # Perhatikan .weekday() untuk datetime object

# Buat DataFrame dari data live untuk OneHotEncoder
live_data_df = pd.DataFrame([[live_suhu_cat, live_jam, live_hari_minggu]], 
                            columns=['suhu_kategori', 'jam', 'hari_minggu'])

# Transformasi data live menggunakan OneHotEncoder yang sama yang digunakan saat pelatihan
# Penting: gunakan ohe_features.transform, BUKAN .fit_transform
live_data_encoded = ohe_features.transform(live_data_df)

# --- Lakukan prediksi ---
prediction_encoded = model.predict(live_data_encoded)[0]
predicted_label = le_label.inverse_transform([prediction_encoded])[0]

# --- Output hasil prediksi dalam format JSON ---
print(json.dumps({"status": "success", "predicted_condition": predicted_label}))