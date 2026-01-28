import json
import re

try:
    with open('phpstan_results_V2.json', 'r') as f:
        data = json.load(f)

    categories = {}
    undefined_errors = []

    files = data.get('files', {})
    total_errors = data.get('totals', {}).get('file_errors', 0)

    for filename, file_data in files.items():
        for err in file_data.get('messages', []):
            msg = err['message']
            
            # Categorize
            cat = 'Other'
            if 'undefined property' in msg.lower(): cat = 'Undefined Property'
            elif 'undefined method' in msg.lower(): cat = 'Undefined Method'
            elif 'access to an undefined property' in msg.lower(): cat = 'Undefined Property'
            elif 'call to an undefined method' in msg.lower(): cat = 'Undefined Method'
            elif 'relation' in msg.lower() and 'not found' in msg.lower(): cat = 'Relationship Not Found'
            elif 'class' in msg.lower() and 'not found' in msg.lower(): cat = 'Class Not Found'
            elif 'parameter' in msg.lower(): cat = 'Parameter Issue'
            elif 'return' in msg.lower(): cat = 'Return Type'
            elif 'should return' in msg.lower(): cat = 'Return Type'
            elif 'access to an offset' in msg.lower(): cat = 'Array Access'
            elif 'expects' in msg.lower(): cat = 'Type Mismatch'
            elif 'comparison' in msg.lower(): cat = 'Comparison Logic'
            elif 'variable' in msg.lower() and 'always exists' in msg.lower(): cat = 'Redundant Check'
            else: cat = msg[:60] + '...'
            
            categories[cat] = categories.get(cat, 0) + 1
            
            if cat in ['Undefined Property', 'Undefined Method', 'Relationship Not Found', 'Class Not Found']:
                 # Simplify filename
                 short_name = filename.split('farm_operation_management/')[-1]
                 undefined_errors.append(f"{short_name}:{err['line']} - {msg}")

    print("--- Error Categories (V2) ---")
    print(f"Total Errors: {total_errors}")
    for cat, count in sorted(categories.items(), key=lambda x: x[1], reverse=True):
        print(f"{count}: {cat}")

    print("\n--- Relationship Not Found Errors ---")
    for err in undefined_errors:
        if 'Relation' in err:
             print(err)
             
except Exception as e:
    print(f"Error parsing JSON: {e}")
