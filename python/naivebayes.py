import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder, OneHotEncoder
from sklearn.naive_bayes import CategoricalNB
from sklearn.metrics import accuracy_score, classification_report
import joblib
from datetime import datetime
import os

# --- Bagian 1: Pengaturan Path dan Pemuatan Data ---
CSV_FILE_PATH = '../data/data_suhu_training.csv' 

# Periksa apakah file CSV ada
if not os.path.exists(CSV_FILE_PATH):
    print(f"Error: File CSV tidak ditemukan di '{CSV_FILE_PATH}'. Pastikan nama dan path-nya benar.")
    exit()

df = pd.read_csv(CSV_FILE_PATH)

print(f"Data berhasil dimuat dari {CSV_FILE_PATH}. {len(df)} baris.")
print("Kolom data:")
print(df.columns)
print("\n5 baris pertama:")
print(df.head())

# --- Bagian 2: Pra-pemrosesan Data (Sama seperti sebelumnya) ---

df['tanggal'] = pd.to_datetime(df['tanggal'], errors='coerce')
df.dropna(subset=['tanggal'], inplace=True) 

df['jam'] = df['tanggal'].dt.hour
df['hari_minggu'] = df['tanggal'].dt.dayofweek

df['nilai_temperatur'] = pd.to_numeric(df['nilai_temperatur'], errors='coerce')
df.dropna(subset=['nilai_temperatur'], inplace=True)

def categorize_temperature(temp):
    temp = float(temp) 
    if temp >= 22 and temp <= 25: 
        return 'Sangat Nyaman'
    elif (temp >= 20 and temp < 22) or (temp > 30 and temp <= 33):
        return 'Kurang Nyaman'
    else: 
        return 'Tidak Nyaman'

df['suhu_kategori'] = df['nilai_temperatur'].apply(categorize_temperature)

if 'label_kondisi' not in df.columns:
    print("\nError: Kolom 'label_kondisi' tidak ditemukan di CSV Anda.")
    print("Pastikan Anda menambahkan kolom ini (manual atau dari skrip export PHP).")
    exit()

X = df[['suhu_kategori', 'jam', 'hari_minggu']]
y = df['label_kondisi']

print("\nFitur dan Target setelah pra-pemrosesan:")
print(X.head())
print(y.head())

# --- Bagian 3: Encoding Fitur Kategorikal (Sama seperti sebelumnya) ---

le_label = LabelEncoder()
y_encoded = le_label.fit_transform(y)

ohe_features = OneHotEncoder(handle_unknown='ignore', sparse_output=False)
X_encoded = ohe_features.fit_transform(X)

print("\nShape X_encoded (setelah OneHotEncoder):", X_encoded.shape)

# --- Bagian 4: Pembagian Data: Training dan Testing (Sama seperti sebelumnya) ---
X_train, X_test, y_train, y_test = train_test_split(X_encoded, y_encoded, test_size=0.2, random_state=42)

print(f"\nUkuran data training: {len(X_train)} samples")
print(f"Ukuran data testing: {len(X_test)} samples")

# --- Bagian 5: Pelatihan Model Naive Bayes (Sama seperti sebelumnya) ---
model = CategoricalNB()
model.fit(X_train, y_train)

print("\nModel Naive Bayes berhasil dilatih!")

# --- Bagian 6: Evaluasi Model (Sama seperti sebelumnya) ---
y_pred = model.predict(X_test)

accuracy = accuracy_score(y_test, y_pred)
print(f"\nAccuracy Model pada data testing: {accuracy:.2f}")

print("\nLaporan Klasifikasi:")
print(classification_report(y_test, y_pred, target_names=le_label.classes_))

# --- Bagian 7: Menyimpan Model dan Encoders ---
MODEL_DIR = '../models/' 
if not os.path.exists(MODEL_DIR):
    os.makedirs(MODEL_DIR) # Buat folder jika belum ada

joblib.dump(model, os.path.join(MODEL_DIR, 'naive_bayes_model.pkl'))
joblib.dump(le_label, os.path.join(MODEL_DIR, 'label_encoder.pkl'))
joblib.dump(ohe_features, os.path.join(MODEL_DIR, 'one_hot_encoder.pkl'))
joblib.dump(categorize_temperature, os.path.join(MODEL_DIR, 'categorize_temperature_func.pkl'))

print(f"\nModel dan Encoders disimpan di folder '{MODEL_DIR}'.")

# ... (bagian sebelumnya dari naivebayes.py tetap sama) ...

# --- Bagian 8: Contoh Prediksi Data Baru ---
print("\n--- Contoh Prediksi Data Baru ---")

# Data sensor live (Contoh)
live_temp = 25.5
live_time = datetime.now() # Ini adalah objek datetime.datetime standar

# Pra-pemrosesan data live (HARUS SAMA dengan pra-pemrosesan data pelatihan)
live_suhu_cat = categorize_temperature(live_temp)

# FIX: Akses .hour dan .weekday() langsung dari objek datetime
live_jam = live_time.hour              
live_hari_minggu = live_time.weekday() 

# Buat DataFrame dari data live untuk OneHotEncoder
live_data_df = pd.DataFrame([[live_suhu_cat, live_jam, live_hari_minggu]], 
                            columns=['suhu_kategori', 'jam', 'hari_minggu'])

# Transformasi data live menggunakan OneHotEncoder yang sama yang digunakan saat pelatihan
live_data_encoded = ohe_features.transform(live_data_df)

# Lakukan prediksi
prediction_encoded = model.predict(live_data_encoded)[0]
predicted_label = le_label.inverse_transform([prediction_encoded])[0]

print(f"\nData Masukan: Suhu={live_temp}°C, Waktu={live_time.strftime('%H:%M')}")
print(f"Kategori Suhu: {live_suhu_cat}")
print(f"Kategori Jam: {live_jam}")
print(f"Kategori Hari: {live_hari_minggu}")
print(f"Prediksi Kondisi: {predicted_label}")