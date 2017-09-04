# blog-symfony
I created a little blog to learn symfony 3

## Getting Started
TO-DO

## API

### Returned formats
As a REST API, it can return datas in 2 formats : **JSON** and **XML**. The platform read the **Accept** HTTP Header, so if you want JSON datas, you have to set the **Accept -> application/json** and for XML : **Accept -> application/xml**. By default, the platform return JSON datas.

### Public API
Some ressources are public (don't need authentification), you just have to type one of the url below this text and you will have a response.

| Path                               | Method   | Summary                                   |
| ---------------------------------- | -------- | ----------------------------------------- |
| .../api/v1/posts                   | GET      | All posts                                 |
| .../api/v1/posts/{id}              | GET      | One specific post                         |
| .../api/v1/categories              | GET      | All categories                            |
| .../api/v1/categories/{id}/posts   | GET      | All posts contain in a specific categorie |

### Private API
These ressouces are private. To access them, **you must have an account on the website** (no specific role), and get a token from the API to prove your entity when you send your request to see private ressources.

1. You have to authenticate to the API and get a token. To do that call the url : **myUrl/api/v1/auth** with a specific http header : Authorization => Basic base64(username:password) (e.g : Basic dGVzdDp0ZXN0). In production **you must use https over that**. If your credentials are good, the api will return your token (24 hours limited time). Each time your token will expired you have to do this process again (a "401 token expired" error will display if the token expired).

2. Call one of the url below this text with your token in the http header **X-Auth-Token**.

| Path                       | Method | Summary                 | HTTP Headers            | POST params             |
| -------------------------- | ------ | ----------------------- | ----------------------- | ----------------------- |
| .../api/v1/categories      | POST   | Create a new category   | X-Auth-Token : {token}  | name : {category name}  |


## Technical Documentation
You can find all technical documentation to understand and continu the project on the [wiki page](https://github.com/gollgot/blog-symfony/wiki).
