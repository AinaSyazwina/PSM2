
from flask import Flask, jsonify
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sqlalchemy import create_engine

app = Flask(__name__)
engine = create_engine('mysql+pymysql://root:@localhost/library')

@app.route('/recommend/<member_id>')  
def recommend(member_id):
    books = pd.read_sql("SELECT * FROM books", engine)
    issues = pd.read_sql("SELECT * FROM issuebook", engine)

    
    data = pd.merge(issues, books, left_on='bookID', right_on='book_acquisition')

   
    user_data = data[data['memberID'] == member_id]  
    if user_data.empty:
        return jsonify(["No recommendations available"])

    print("User Data DataFrame:")
    print(user_data)

    other_books = books[~books['book_acquisition'].isin(user_data['book_acquisition'])]

    print("Other Books DataFrame:")
    print(other_books)

    user_genres = user_data['genre'].str.get_dummies(sep=',')
    other_genres = other_books['genre'].str.get_dummies(sep=',')

    all_genres = user_genres.columns.union(other_genres.columns)
    user_genres = user_genres.reindex(columns=all_genres, fill_value=0)
    other_genres = other_genres.reindex(columns=all_genres, fill_value=0)

    similarity = cosine_similarity(user_genres, other_genres)
    scores = similarity.sum(axis=0)
    recommended_indices = scores.argsort()[-3:][::-1]
    recommended_books = other_books.iloc[recommended_indices]

    return jsonify(recommended_books['Title'].tolist())

if __name__ == '__main__':
    app.run(debug=True)