WebSocket Test


Установка приложения:

git clone https://github.com/Kuzaevel/test_websocket.git

Устанавливаем зависимости с помощью composer
- composer install

Создаем базу данных MySql test_socket

Восстанавливаем базу данных с помощью миграций
$ yii migrate

Запускаем из командной строки приложение:
$ yii chat/run

Примеры запросов в файле в корне проекта:
- ws://test.local:8080?token=44H67gG2NAuF2Ng0IgnO_ofNJK4iEu13 
   {
     "method": "sendMessage",
     "data": {
       "text": "1text{{$randomLoremText}}"
     }
   }

- ws://test.local:8080?token=ykMp9PKzPv39bDQL78UwsxzZunwVVgO5
   {
      "method": "sendMessage",
      "data": {
        "text": "2text{{$randomLoremText}}"
      }
   }


Для авторизации используется QueryToken - token:

composer config -g -- disable-tls true

