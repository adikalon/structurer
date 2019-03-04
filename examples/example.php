<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Hellpers\Structurer;

/*
|------------------------------------------------------------------------------
| Статичный способ
|------------------------------------------------------------------------------
|
| Первый параметр принимает абсолютный путь - структуру каталогов, которую
| необходимо выстроить. Недостающие папки будут созданы.
|
| Второй, не обязательный параметр, принимает название файла, который будет
| создан в финальной папке.
|
| Третий, не обязательный параметр, принимает число в восьмеричной системе
| счисления. Устанавливает права доступа для файлов и каталогов в создаваемой
| структуре. По умолчанию - 0777.
|
| Возвращает строку - абсолютный и развернутый путь, который был создан.
|
*/
$path = Structurer::make(__DIR__ . '/../one/two', 'file.txt', 0777);

/*
|------------------------------------------------------------------------------
| Статичный способ с созданием файла и заполнением его контентом
|------------------------------------------------------------------------------
|
| Первый параметр принимает абсолютный путь - структуру каталогов, которую
| необходимо выстроить. Недостающие папки будут созданы. Последняя часть
| структуры пути оценивается как имя файла, который и будет создан.
|
| Второй, не обязательный параметр, принимает текст, который необходимо записать
| в файл.
|
| Третий, не обязательный параметр, принимает булевое значение, где true -
| дописать текст в конец файла, false - записать текст поверх уже находящегося в
|  файле.
|
| Четвертый, не обязательный параметр, принимает число в восьмеричной системе
| счисления. Устанавливает права доступа для файлов и каталогов в создаваемой
| структуре. По умолчанию - 0777.
|
| Возвращает строку - абсолютный и развернутый путь, который был создан.
|
*/
$path = Structurer::cmake(__DIR__ . '/../file.txt', 'Test string', true, 0777);


/* ========================================================================= */



/*
|------------------------------------------------------------------------------
| Более функциональный, объектный способ
|------------------------------------------------------------------------------
|
| При создании объекта необходимо передать абсолютный путь к каталогу, в
| котором будет проиходить вся работа. Обычно это корень приложения.
|
*/
$structurer = new Structurer(__DIR__ . '/../');

/*
|------------------------------------------------------------------------------
| Цепочка вызовов ниже, делает следующее:
|------------------------------------------------------------------------------
|
| 1. Метод folder() создает папку "one" и осуществляет в нее переход;
| 2. В папке "one" создается папка "two";
| 3. В папке "two" создается папка по имени текущей даты (об этом ниже);
| 4. В папке с именем даты создается папка "three";
| 5. Метод back() возвращает на уровень выше - в папке с именем даты;
| 6. Метод file() создает файл "test.txt";
| 7. Рядом создается файл с именем текущей даты и расширением .txt;
| 8. Метод content() записывает текст "Test string" в файл с именем текущей
| даты.
|
*/
$result = $structurer
    ->folder('one')
    ->folder('two')
    ->folder(Structurer::d('Y-m-d'))
    ->folder('three')
    ->back()
    ->file('test.txt')
    ->file(Structurer::d('Y-m-d') . '.txt')
    ->content('Test string');

/*
|------------------------------------------------------------------------------
| По итогу работы можно получить некоторую информацию
|------------------------------------------------------------------------------
|
| Метод path() вернет абсолютный путь к месту на котором был указатель.
| Например, в данном случае: .../hellpers/Structurer/one/two/2018-11-23
|
| Метод ls() вернет массив со списком файлов и папок в папке, на которой
| находится указатель. Например, в данном случае:
| Array
| (
|     [0] => 2018-11-23.txt
|     [1] => test.txt
|     [2] => three
| )
|
*/
$path = $result->path();
$ls   = $result->ls();



/* ========================================================================= */



/*
|------------------------------------------------------------------------------
| Документация ко всем методам
|------------------------------------------------------------------------------
|
| При создании объекта передается абсолютный путь к директории внутри которой
| происходит вся работа. Этот путь считается корнем приложения.
| В процессе работы объект может установить курсор на файл с которым работает.
|
| -----------------------------------------------------------------------------
| folder()
| -----------------------------------------------------------------------------
| Создает папку в корне приложения.
| Принимает первым параметром - название для создаваемой папки, вторым - права
| доступа для нее. Права доступа задаются в восьмеричной системе счисления,
| параметр является необязательным и по умоланию установлен в 0777. Все
| запрещенные (в зависимости от операционной системы в которой выполняется
| скрипт) для названия символы будут заменены нижним подчеркиванием.
| Возвращает новый объект класса Structurer, где корнем установлена созданная
| папка.
|
| -----------------------------------------------------------------------------
| back()
| -----------------------------------------------------------------------------
| Поднимает текущий каталог на уровень выше. Аналогичен директории "..".
| Может быть удобным, когда внутри одного каталога необходимо создать несколько
| других. Поскольку при создании каталога в него осуществляется переход back()
| позволят вернуться на уровень выше.
| Возвращает новый объект класса Structurer, где корнем установлена папка
| стоящая выше по уровню.
|
| -----------------------------------------------------------------------------
| file()
| -----------------------------------------------------------------------------
| Создает файл в корне приложения.
| Принимает те же параметры, то и метод folder() с той лишь разницей, что
| создает файл, а не папку. Обработка параметров происходит аналогичным образом.
| Возвращает текущий объект с установленным курсором на созданный файл.
|
| -----------------------------------------------------------------------------
| content()
| -----------------------------------------------------------------------------
| Записывает текст в файл на котором установлен курсор.
| Первым параметром сам текст. Второй, необязательный параметр, принимает
| булевое значение, где true - дописать текст в конец файла, false - записать
| текст поверх уже находящегося в файле. Второй параметр установлен в false по
| умолчанию.
|
| -----------------------------------------------------------------------------
| mode()
| -----------------------------------------------------------------------------
| Смена прав доступа, владельца, группы для файла/папки.
| Первым параметром принимает массив, где в качестве ключа может выступать
| название одной из функций php: chmod(), chown() или chgrp(), а в качестве
| значений - те же параметры, как и в оригинальных функциях.
| По умолчанию права меняются либо для текущей директории, либо, при
| налачиикурсора установленного на файл, у файла. Если при налачии файлового
| курсора необходимо применить функцию по отношению к директории, тогда вторым
| параметром необходимо передать - true.
|
| -----------------------------------------------------------------------------
| path()
| -----------------------------------------------------------------------------
| Получить абсолютный путь к текущей директории.
| Возвращает строку содержащую развернутый путь к текущей директории.
| Если передать true, тогда, при наличии курсора установленного на файле, путь
| будет возвращен вместе с файлом.
|
| -----------------------------------------------------------------------------
| ls()
| -----------------------------------------------------------------------------
| Получить список файлов внутри текущей директории.
| Возвращает массив со списком файлов и папок находящихся в текущей директории.
|
| -----------------------------------------------------------------------------
| d()
| -----------------------------------------------------------------------------
| Создать шаблон для преобразования функцией date().
| Статический метод.
| Порой очень удобно создавать файлы и/или папки, имена которых содержали бы
| элементы даты. Например, для записи логов. Передавать уже готовое название не
| всегда практично, т.к. если скрипт работает продолжительное время и переходит
| из одних суток в другие, тогда название продолжает соответствовать дню
| предыдущему.
| Метод принимает шаблон результирующей строки, как и php функция - date() и
| возвращает этот же шаблон но обернутый в специальный внутриклассовый, его уже
| можно использовать давая названия папкам и файлам, т.к. обрабатываться
| функцией date() название будет непосредтвенно в момент создания, т.е. будет
| всегда актуальным.
|
| -----------------------------------------------------------------------------
| make()
| -----------------------------------------------------------------------------
| Простой статический метод, для создания многоуровневой структуры.
| Принимает первым параметром абсолютный путь, структуру которого необходимо
| реализовать. Второй, необзятельный параметр, принимает название для файла,
| если его необходимо создать и положить по указанному в первом параметре пути.
| Третий, необзятельный параметр, принимает шаблон прав доступа в восьмеричной
| системе счисления для создаваемых файлов и папок, по умолчанию установлен в
| 0777.
| Возвращает абсолютный и развернутый путь, который был воссоздан, если был
| создан и файл, тогда вместе с ним.
|
*/
