![License CC ](https://img.shields.io/badge/LICENSE-CC_BY_NC_ND-%23EF9421?style=flat-square&logo=php)
![PHP Version 5.6](https://img.shields.io/badge/PHP-5.6-%23777BB4?style=flat-square&logo=php)
![Designed For InternetExplorer 6](https://img.shields.io/badge/BROWSER-IE6-%230076D6?style=flat-square&logo=Internet-Explorer)
![GitHub last commit](https://img.shields.io/github/last-commit/jkulvichs-sandbox/rodbauto?style=flat-square)

# 🏢 RODBAuto Backend

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

//TODO:

- `ANY /api/test.php?{any} -d {any}`  
  Проверка работоспособности сервера, вывод тех.страницы
- `GET /api/persons/search?query={query}`
    - `{query}` - Универсальный поиск по ФИО, году рождения, личному номеру итд.
    - //TODO: filters: `{birth_year}`, `{recruiting_office}`, `{local_command}`

## ➕ Создание нового действия

1. Создайте класс в Actions/ с названием действия Action*
   наследованный от Action и реализующий метод Execute($ctx)
   в пространстве имён Actions
2. Добавьте константу имени действия в Actions/Actions.php и импортируйте файл действия
3. Создайте файл вызова перенаправления в api/ с именем действия и кодом перенаправления
4. Добавьте логику перенаправления в App.php::Main

## ➕ Создание новой модели

1. Добавьте класс модели в Postgres/Models с именем модели в пространстве имён Models унаследованный от Model
   соответствующий ему
2. Укажите связанную таблицу и карту соответствий в классе модели

## ♻ Жизненный цикл

```
+-< +---[ api ]---+ -> [ТОЧКА ВХОДА] Скрипты вызова действий
|
+---+
    V
+-< +---[ Run ]---+ -> Скрипт запуска приложения
|
+-> +---[ App ]---+
    | Init        | -> Конфиг, БД
+-< | Main        | -> Роуты действий
|   | Finalize    | -> Очистка ресурсов
|   +-------------+
|     
+-> +---[ Postgres ]-----+
|   | Postgres           | -> ORM БД
|   | +---[ Models ]---+ | -> Модели
|   | | Model >-+      | | -> Базовая функциональность модели
|   | | ...   <-+      | | -> Конкретные реализации
|   | +----------------+ |
|   +--------------------+
|
+-> +---[ Actions ]---+
    | Actions         | -> Импорт модуля и константы
    | Action          | -> Базовый класс действия
    | Context         | -> Контекст действия с параметрами запроса и БД
    | Response        | -> Структура унифицированного ответа сервера
    | Action*         | -> Реализации действий
    | ...             |
    +-----------------+
```

## ✅ TODO

### Поиск людей по:

- [X] ФИО, причём по всему вместе (Ярослав Андреевич)
- [X] Году рождения
- [ ] Названию призывного пункта (id первые 8 цифр из priz01.pnom)
  с ними по таблице r8012
- [X] Личный номер
- [ ] Предварительная команда (кастомное поле, по умолчанию 700 для пустых)

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
