# autodeploy

```
{
  "require": {
    "srclab/autodeploy": "^2.0"
  },
  "repositories": [
    {
      "type": "github",
      "url":  "https://github.com/srclab/autodeploy.git"
    }
  ]
}

```

****

## Настройка уведомлений в Slack

1. Настроить Incoming WebHooks в Slack - https://slack.com/apps/A0F7XDUAZ-incoming-webhooks
2. Скопировать полученный Webhook URL
3. Настроить .env файл проекта:
   1. Включить уведомления в слак с помощью `APP_AUTO_DEPLOY_SLACK_NOTIFICATION_ENABLED=true`
   2. Добавить скопированный Webhook URl в `APP_AUTO_DEPLOY_SLACK_NOTIFICATION_HOOKS_URL={Webhook URl}`

#### Зависимости для уведомлений в Slack:

```
{
  "require": {
    "laravel/slack-notification-channel": "^2.3",
  }
}

```