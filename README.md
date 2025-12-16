# School Test Generator

## Sending build folder to server

```bash
rsync public/build -r -e "ssh -p PORT" HOST:public_html/school-test-generator/public
```
## Sending env file to server
```bash
rsync .env -e "ssh -p PORT" HOST:public_html/school-test-generator
```


