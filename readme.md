![License CC ](https://img.shields.io/badge/LICENSE-CC_BY_NC_ND-%23EF9421?style=flat-square&logo=php)
![PHP Version 5.6](https://img.shields.io/badge/PHP-5.6-%23777BB4?style=flat-square&logo=php)
![Designed For InternetExplorer 6](https://img.shields.io/badge/BROWSER-IE6-%230076D6?style=flat-square&logo=Internet-Explorer)
![GitHub last commit](https://img.shields.io/github/last-commit/jkulvichs-sandbox/rodbauto?style=flat-square)

# 🏢 RODBAuto || СПБД

> Recruiting Office DataBase Automation Tools  
> Инструменты автоматизации БД Сборного Пункта

> Лицензия **[СС Attribution-NonCommercial-NoDerivs](LICENSE)**
>
> ![License Attribution-NonCommercial-NoDerivs](https://licensebuttons.net/l/by-nc-nd/3.0/88x31.png)
> - **Запрет** коммерческого использования
> - **Запрет** модификации
> - **Разрешен** просмотр и распространение

Спроектировано для поддержки PHP 5.6 и InternetExplorer 6.  
Для внутренних изолированных сетей.

## 🔌 API

Все ответы используют единый формат:

```json5
{
  // Результат ответа
  "data": {},
  // Описание ошибки, если произошла
  "error": {
    // Факт ошибки
    "status": false,
    // Текстовый код|ID ошибки
    "code": "",
    // Описание ошибки
    "message": ""
  },
  // Контекст выполнения action
  "context": {
    // ID действия
    "action": "",
    // Используемый HTTP метод
    "method": "",
    // Распаршенные аргументы
    "args": "",
    // Входящие данные
    "body": ""
  }
}
```

Далее будет приводится только содержание поля `data`

> #### GET /server/api/recruit-offices/list.php

Получение списка соотношения ID призывного пункта к его названию.

Пример ответа:

```json5
[
  {
    "id": "08489495",
    "name": "Октябрьский и Железнодорожный"
  },
]
```

> #### GET /server/api/persons/search.php

Универсальный поиск по ФИО, году рождения, личному номеру, названию призывного пункта и предварительной команде.

Параметры запроса:

- `limit` - Количество результатов поиска для каждой категории. По умолчанию 5, максимум 10
- `filter_birth_year` - //TODO:
- `filter_recruit_office` - //TODO:
- `filter_local_command` - //TODO:

Пример ответа:

```json5
[
  {
    "id": "000000000000000",
    "person_name": "Иванов Иван Иванович",
    "person_birth_year": "2000",
    // Или null
    "person_id": "РА-000000",
    "rec_office_name": "ВК Башмаковского и Пачелмского районов Пензенской области, раб.пос. Башмаково",
    "rec_office_id": "00000000",
    "extra": "{\"localCommand\":\"00\",\"comment\":\"...\"}"
  }
]
```

## ➕ Создание нового действия

1. Создайте класс в Actions/Action с названием действия Action*
   наследованный от Action и реализующий метод Execute($ctx)
   в пространстве имён Action
2. Добавьте константу имени действия в Actions/Action/all.php и импортируйте файл действия
3. Создайте файл вызова перенаправления в api/ с именем действия и кодом перенаправления
4. Добавьте логику перенаправления в App.php::Main

## ➕ Создание новой модели

1. Добавьте класс модели в Postgres/Models с именем модели в пространстве имён Models унаследованный от Model
   соответствующий ему
2. Укажите связанную таблицу и карту соответствий в классе модели

## ♻ Жизненный цикл backend

```
+-< +---[ api ]---+ -> [ТОЧКА ВХОДА] Скрипты вызова действий
|   | ...         |
|   +-------------+
|
+---+
    V
+-< +---[ Run ]---+ -> Скрипт запуска приложения
|
+----> +---[ App ]---+ -> КОнтроллер приложения
       | Init        | -> Конфиг, БД
+----< | Main        | -> Роуты действий
|      | Finalize    | -> Очистка -> Базовый класс действияресурсов
|  +-< +-------------+
|  |
|  +-> +---[ Postgres ]-----+ -> ORM и содели БД
|      | Postgres           | -> ORM БД
|      | +---[ Models ]---+ | -> Модели
|      | | Model >-+      | | -> Базовая функциональность модели
|      | | ...   <-+      | | -> Конкретные реализации
|      | +----------------+ |
|      +--------------------+
|
+-> +---[ Actions ]-----------+ -> Контроллер действий        
    | Context                 | -> Контекст действия с параметрами запроса и БД
    | Response                | -> Структура унифицированного ответа сервера
    | Errors                  | -> Описание констант ошибок
+---< +---[ Action ]--------+ |
|   | | Action              | | -> Базовый класс действия
|   | | ActionError         | | -> Ответ об ошибке
|   | | ActionPersonsSearch | | -> Обработка запроса поиска
|   | | ...                 | |
|   | +---------------------+ |
|   +-------------------------+
|   
+-> +---[ Structures ]---+ -> Структуры упаковки
    | PersonCardReply    | -> Карточка призывника
    | ...                |
    +--------------------+
```

## ✅ TODO

### Поиск людей по:

- [X] ФИО, причём по всему вместе (Ярослав Андреевич)
- [X] Году рождения
- [X] Названию призывного пункта (id первые 8 цифр из priz01.pnom)
  с ними по таблице r8012
- [X] Личный номер
- [X] Предварительная команда (кастомное поле, по умолчанию 700 для пустых)

### Фильтр:

- [ ] Год рождения
- [ ] Призывной пункт
- [ ] Предварительная команда

### Вывод информации в краткой карте поиска:

- [ ] Редактирование поля с предварительной командой который записывается как JSON в priz10.p100
- [ ] ФИО
- [ ] Год рождения
- [ ] Жетон / Личный номер
- [ ] Военкомат
- [ ] Примечание (редактируемое)

> В priz10.p100 иногда может лежать текст с комментом.
> В таком случае пихать его в комментарий в JSON'е
