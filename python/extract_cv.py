import sys
import json
import re
import os
from docx import Document
from PyPDF2 import PdfReader

def extract_text(filepath):
    text = ""
    if filepath.endswith('.txt'):
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            text = f.read()
    elif filepath.endswith('.pdf'):
        reader = PdfReader(filepath)
        for page in reader.pages:
            text += page.extract_text() + "\n"
    elif filepath.endswith('.docx'):
        doc = Document(filepath)
        for para in doc.paragraphs:
            if para.text.strip():
                text += para.text + "\n"
    return text

def extract_email(text):
    match = re.search(r'[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}', text, re.I)
    return match.group(0) if match else ""

def extract_phone(text):
    match = re.search(r'(?:\+212|0)[0-9\s\-]{9,13}', text)
    return match.group(0).strip() if match else ""

def extract_name(text):
    lines = text.split('\n')
    for line in lines:
        line = line.strip()
        if re.match(r'^[A-Za-zÀ-ÿ]+\s+[A-Za-zÀ-ÿ]+$', line):
            parts = line.split()
            if len(parts) >= 2:
                return parts[0].capitalize(), parts[1].upper()
    return "", ""

def extract_section(text, keywords):
    """Extrait une section complète entre deux titres"""
    lines = text.split('\n')
    in_section = False
    content = []
    for line in lines:
        line_upper = line.upper()
        # Début de section
        if any(kw.upper() in line_upper for kw in keywords):
            in_section = True
            continue
        # Fin de section (autre section)
        if in_section and re.match(r'^[A-Z]{4,}', line.strip()):
            break
        if in_section and line.strip():
            clean_line = re.sub(r'^[\-\•\*\▪]\s*', '', line)
            content.append(clean_line)
    return '\n'.join(content).strip()

if __name__ == "__main__":
    filepath = sys.argv[1]
    text = extract_text(filepath)
    
    result = {
        "nom": extract_name(text)[1],
        "prenom": extract_name(text)[0],
        "email": extract_email(text),
        "telephone": extract_phone(text),
        "competences": extract_section(text, ['COMPETENCES', 'Compétences', 'SKILLS']),
        "langues": extract_section(text, ['LANGUES', 'Langues', 'LANGUAGES']),
        "diplomes": extract_section(text, ['DIPLOMES', 'Diplômes', 'FORMATION', 'EDUCATION']),
        "experiences": extract_section(text, ['EXPERIENCES', 'Expériences', 'WORK EXPERIENCE']),
        "certifications": extract_section(text, ['CERTIFICATIONS', 'Certifications']),
        "texte_brut": text[:5000]
    }
    
    print(json.dumps(result, ensure_ascii=False))