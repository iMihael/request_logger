# Request Logger Telegram Bot

To install web hook:
> curl -F "certificate=@/path/to/pem/or/cert/file"  https://api.telegram.org/bot<token>/setWebhook?url=<your web hook url>

Configure `config/mongo.php` and `config/params.php`

# Bot commands
- start - Generate new token
- subscribe {tag} - Subscribe to tag
- unsubscribe {tag} - Unsubscribe from tag
