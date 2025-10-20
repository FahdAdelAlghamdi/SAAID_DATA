# SAAID/app.py

from flask import Flask, render_template, request, redirect, url_for, session
import pandas as pd
import numpy as np
import random
import requests # Needed for real payment gateway API calls
# from sklearn.model_selection import train_test_split # Can be used later for advanced AI selection

app = Flask(__name__)
# IMPORTANT: Change this secret key to a complex, secret value in a production environment
app.secret_key = 'VERY_SECRET_SAUDI_KEY_2030' 

# ------------------------------------------------
# 0. Payment Gateway Settings (PayTabs Mock)
# ------------------------------------------------
# NOTE: Replace these placeholder values with your actual keys from PayTabs
PAYTABS_PROFILE_ID = "YOUR_PAYTABS_PROFILE_ID"
PAYTABS_SERVER_KEY = "YOUR_SERVER_KEY_FROM_PAYTABS" # Must be kept highly secret
PAYTABS_BASE_URL = "https://secure.paytabs.sa/payment/request"
RETURN_URL = "http://127.0.0.1:5000/payment_callback" # User returns here after payment
WEBHOOK_URL = "http://127.0.0.1:5000/payment_webhook" # PayTabs sends final confirmation here

# ------------------------------------------------
# 1. Mock Question Database (3000 Questions)
# ------------------------------------------------
# For production: Replace this function with pd.read_csv('questions.csv')
def load_questions():
    """Generates a mock dataframe for 3000 questions."""
    data = {
        'id': range(1, 3001),
        'question_text': [f"What is the rule regarding [Topic {i % 5 + 1}]? (Question {j})" 
                          for i in range(5) for j in range(600)],
        'correct_answer': [random.choice(['A', 'B', 'C', 'D']) for _ in range(3000)],
        'topic_id': [i % 5 + 1 for i in range(3000)],
        'difficulty': np.random.randint(1, 10, 3000)
    }
    df = pd.DataFrame(data)
    return df

questions_df = load_questions()

# ------------------------------------------------
# 2. Stage 1 Logic: AI/ML Question Selection
# ------------------------------------------------

def select_ai_questions(df, count=100):
    """
    Intelligently selects 100 questions ensuring diversity in difficulty and topics.
    """
    easy = df[df['difficulty'] <= 3]
    medium = df[(df['difficulty'] > 3) & (df['difficulty'] <= 6)]
    hard = df[df['difficulty'] > 6]
    
    # Select samples (e.g., 20% easy, 50% medium, 30% hard)
    q_easy = easy.sample(n=int(count * 0.20), replace=True)
    q_medium = medium.sample(n=int(count * 0.50), replace=True)
    q_hard = hard.sample(n=int(count * 0.30), replace=True)
    
    # Combine and shuffle the questions
    selected_questions = pd.concat([q_easy, q_medium, q_hard]).sample(frac=1).reset_index(drop=True)
    
    return selected_questions.head(count).to_dict('records')

# ------------------------------------------------
# 3. Core User State Management
# ------------------------------------------------

def is_user_paid():
    return session.get('is_paid', False)
    
def set_user_as_paid():
    # Placeholder for updating a REAL database after payment success
    session['is_paid'] = True
    session['exam_stage'] = 0 # Reset exam stage on payment