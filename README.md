
Backend Exercise with restfull endpoints


chatapp
=======

A Symfony project created on April 23, 2016, 2:29 pm.
=======
# chatapp

Backend Exercise

For checking REST end points :GET /user/{user_id}/conversation/{conversation_id}"

Example:/user/2/conversation/1

And POST(sender_id=?, receiver_id=?, text=?):"/conversation/{conversation_id}/message"

Example:/conversation/1/message  and post parametrs (sender_id=1, receiver_id=3, text=3)
Use the any REST client (i used Postman for chrome, add as extensions)

Postman link:https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop

Installation: copy folder with project, install symfony follow links:

http://symfony.com/doc/current/book/installation.html

run php bin/console server:run in project folder