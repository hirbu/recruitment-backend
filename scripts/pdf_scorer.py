#!/usr/bin/env python3

import sys
import PyPDF2
import re
from typing import List

def count_tag_occurrences(pdf_path: str, tags: List[str]) -> int:
    """
    Count occurrences of tags in a PDF file and return the total score.
    
    Args:
        pdf_path (str): Path to the PDF file
        tags (List[str]): List of tags to search for
        
    Returns:
        int: Total score (sum of all tag occurrences)
    """
    try:
        with open(pdf_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            
            total_score = 0
            
            for page in pdf_reader.pages:
                text = page.extract_text().lower()
                
                for tag in tags:
                    count = len(re.findall(r'\b' + re.escape(tag.lower()) + r'\b', text))
                    total_score += count
            
            return total_score
            
    except Exception as e:
        print(f"Error processing PDF: {str(e)}", file=sys.stderr)
        return 0

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python pdf_scorer.py <pdf_path> <tag1> [tag2 ...]", file=sys.stderr)
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    tags = sys.argv[2:]
    
    score = count_tag_occurrences(pdf_path, tags)
    print(score) 