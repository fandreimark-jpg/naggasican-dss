import sys
import json
import os
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import cross_val_score

MODEL_PATH = os.path.join(os.path.dirname(__file__), 'model_cache.pkl')

def get_model():
    try:
        import joblib
        if os.path.exists(MODEL_PATH):
            return joblib.load(MODEL_PATH)
        model = train_model()
        joblib.dump(model, MODEL_PATH)
        return model
    except ImportError:
        return train_model()


def train_model():
    """
    Training data base sa DepEd SHS grading system:
    - LOW RISK      : 90 - 100 (Outstanding / Very Satisfactory)
    - MODERATE RISK : 75 - 89  (Satisfactory / Fairly Satisfactory)
    - HIGH RISK     : 60 - 74  (Did Not Meet Expectations)

    Mas maraming training samples = mas accurate ang model
    """
    # ============================================================
    # LOW RISK — 90 to 100 (30 samples)
    # ============================================================
    low_risk = [
        [90.0], [90.5], [91.0], [91.5], [92.0],
        [92.5], [93.0], [93.5], [94.0], [94.5],
        [95.0], [95.5], [96.0], [96.5], [97.0],
        [97.5], [98.0], [98.5], [99.0], [99.5],
        [100.0],[90.2], [91.8], [93.3], [94.7],
        [95.8], [96.3], [97.8], [98.3], [99.2],
    ]

    # ============================================================
    # MODERATE RISK — 75 to 89 (30 samples)
    # ============================================================
    moderate_risk = [
        [75.0], [75.5], [76.0], [76.5], [77.0],
        [77.5], [78.0], [78.5], [79.0], [79.5],
        [80.0], [80.5], [81.0], [81.5], [82.0],
        [83.0], [84.0], [85.0], [86.0], [87.0],
        [88.0], [89.0], [75.3], [76.8], [78.3],
        [80.3], [82.7], [84.5], [86.5], [88.5],
    ]

    # ============================================================
    # HIGH RISK — 60 to 74 (30 samples)
    # ============================================================
    high_risk = [
        [74.9], [74.0], [73.5], [73.0], [72.5],
        [72.0], [71.5], [71.0], [70.5], [70.0],
        [69.5], [69.0], [68.5], [68.0], [67.0],
        [66.0], [65.0], [64.0], [63.0], [62.0],
        [61.0], [60.0], [74.5], [73.3], [71.8],
        [70.3], [68.3], [66.7], [63.5], [61.5],
    ]

    X_train = np.array(low_risk + moderate_risk + high_risk)
    y_train = np.array(
        [0] * len(low_risk) +       # 0 = low
        [1] * len(moderate_risk) +  # 1 = moderate
        [2] * len(high_risk)        # 2 = high
    )

    model = RandomForestClassifier(
        n_estimators=200,   # mas maraming trees = mas accurate
        random_state=42,
        max_depth=5,
        min_samples_split=2,
        min_samples_leaf=1,
    )
    model.fit(X_train, y_train)

    # ============================================================
    # Cross-validation — para ma-verify ang accuracy ng model
    # Isinasave ang score sa log file para makita mo
    # ============================================================
    try:
        scores  = cross_val_score(model, X_train, y_train, cv=5, scoring='accuracy')
        avg     = round(scores.mean() * 100, 2)
        log_path = os.path.join(os.path.dirname(__file__), 'model_accuracy.txt')
        with open(log_path, 'w') as f:
            f.write(f"Cross-validation Accuracy: {avg}%\n")
            f.write(f"Per-fold scores: {[round(s*100,2) for s in scores]}\n")
            f.write(f"Training samples: {len(X_train)}\n")
            f.write(f"  Low risk:      {len(low_risk)} samples\n")
            f.write(f"  Moderate risk: {len(moderate_risk)} samples\n")
            f.write(f"  High risk:     {len(high_risk)} samples\n")
    except Exception:
        pass

    return model


def classify_students(grades_data, model):
    label_map = {0: 'low', 1: 'moderate', 2: 'high'}
    results   = []

    for student in grades_data:
        student_id    = student['student_id']
        average_grade = float(student['average_grade'])

        # Edge case handling — below 60 ay automatic High Risk
        if average_grade < 60:
            results.append({
                'student_id':    student_id,
                'average_grade': average_grade,
                'risk_level':    'high',
                'confidence':    100.0,
            })
            continue

        prediction    = model.predict([[average_grade]])[0]
        probabilities = model.predict_proba([[average_grade]])[0]

        results.append({
            'student_id':    student_id,
            'average_grade': average_grade,
            'risk_level':    label_map[prediction],
            'confidence':    round(float(max(probabilities)) * 100, 2),
        })

    return results


def main():
    if len(sys.argv) < 3:
        print('Usage: classify.py <input_file> <output_file>')
        return

    input_file  = sys.argv[1]
    output_file = sys.argv[2]

    try:
        with open(input_file, 'r') as f:
            grades_data = json.load(f)
    except Exception as e:
        print(f'Error reading input file: {e}')
        return

    if not grades_data:
        with open(output_file, 'w') as f:
            json.dump([], f)
        return

    model   = get_model()
    results = classify_students(grades_data, model)

    try:
        with open(output_file, 'w') as f:
            json.dump(results, f)
    except Exception as e:
        print(f'Error writing output file: {e}')


if __name__ == '__main__':
    main()