### First task query:

```
SELECT u.id as ID, CONCAT(u.first_name, ' ', u.last_name) as Name,
b.author as Author, GROUP_CONCAT(b.name SEPARATOR ', ') as Books
    FROM users u
    LEFT OUTER JOIN user_books ub ON u.id = ub.user_id 
        AND (u.age BETWEEN 7 AND 17)
    LEFT OUTER JOIN books b ON b.id = ub.book_id

GROUP BY ub.user_id
HAVING (COUNT(ub.user_id) = 2 AND COUNT(DISTINCT b.author) = 1);
```