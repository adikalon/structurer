<?php

namespace Hellpers;

use Exception;
use DateTime;

/**
 * Работа с файловой системой
 * 
 * @author adikalon
 */
class Structurer
{
    /**
     * @var string Абсолютный путь к корню приложения
     */
    private $core = '';

    /**
     * @var string Имя последнего файла с которым велась работа
     */
    private $file = '';

    /**
     * @var array Шаблон-обертка для символов, которые необходимо прогнать
     * через DateTime::format()
     */
    private static $template = [
        'before' => '::d->(::',
        'after'  => '::);::',
    ];

    /**
     * Инициализация
     * 
     * @param string $core Абсолютный путь к корню приложения
     * @throws Exception
     */
    public function __construct(string $core)
    {
        $this->core = realpath($core);

        if (!$this->core or !is_dir($this->core)) {
            throw new Exception("Некорректный адрес приложения: {$this->core}");
        }

        $this->core = Pather::upath($this->core);
        $this->core = Pather::rstrim($this->core);

        unset($core);
    }

    /**
     * Создание папки и/или переход в нее
     * 
     * @param string $name Имя для папки
     * @param int $mode (optional) Права доступа (в восьмеричной системе
     * счисления)
     * @return self Новый объект, с новым корневым путем
     */
    public function folder(string $name, int $mode = 0777): self
    {
        $path = '';

        $name = Pather::name($name);
        $path = "{$this->core}/$name";
        $make = self::make($path, '', $mode);

        unset($name, $mode, $path);

        return new self($make);
    }

    /**
     * Подняться директорией выше. Не сработает в корне
     * 
     * @return self Новый объект, с новым корневым путем
     */
    public function back(): self
    {
        $path = '';

        $path = preg_replace('/\/[^\/]+$/ui', '', $this->core);

        return new self($path);
    }

    /**
     * Создание файла и/или установка внутреннего курсора на него
     * 
     * @param string $name Имя для файла
     * @param int $mode (optional) Права доступа (в восьмеричной системе
     * счисления)
     * @return self Текущий объект с курсором установленным на файле
     */
    public function file(string $name, int $mode = 0777): self
    {
        $path = '';

        $name       = Pather::name($name);
        $path       = self::make($this->core, $name, $mode);
        $this->file = preg_replace('/.*\//ui', '', $path);

        unset($name, $mode, $path);

        return $this;
    }

    /**
     * Записать текст в файл на котором установлен курсор
     * 
     * @param string $content Текст, который необходимо записать
     * @param bool $append (optional) true - дописать в файл
     * @return self Текущий объект
     * @throws Exception
     */
    public function content(string $content, bool $append = false): self
    {
        $path = '';

        if (!$this->file) {
            throw new Exception(
                "Отсутствует файл для записи. Директория: {$this->core}"
            );
        }

        if ($append) {
            $append = FILE_APPEND;
        }

        $path = "{$this->core}/{$this->file}";

        if (file_put_contents($path, $content, $append) === false) {
            throw new Exception("Не удалось записать в файл: $path");
        }

        unset($content, $append, $path);

        return $this;
    }

    /**
     * Смена прав доступа, владельца, группы для файла/папки
     * 
     * @param array $params Что менять
     * 
     * int $params['chmod'] (optional) - Права доступа (в восьмеричной системе
     * счисления)
     * 
     * mixed $params['chown'] (optional) - Владелец (имя или число)
     * 
     * mixed $params['chgrp'] (optional) - Группа (название или номер)
     * 
     * @param bool $folder (optional) true - применить для текущей директории
     * @return self Текущий объект
     * @throws Exception
     */
    public function mode(array $params, bool $folder = false): self
    {
        $path = '';

        $path = $this->core;

        if ($this->file and !$folder) {
            $path .= "/{$this->file}";
        }

        if (isset($params['chmod'])) {
            if (!chmod($path, $params['chmod'])) {
                throw new Exception("Не удалось изменить режим доступа: $path");
            }
        }

        if (isset($params['chown'])) {
            if (!chown($path, $params['chown'])) {
                throw new Exception("Не удалось изменить владельца: $path");
            }
        }

        if (isset($params['chgrp'])) {
            if (!chgrp($path, $params['chgrp'])) {
                throw new Exception("Не удалось изменить группу: $path");
            }
        }

        unset($params, $folder, $path);

        return $this;
    }

    /**
     * Получить абсолютный путь к текущей директории
     * 
     * @param bool $file (optional) true - вернуть вместе с файлом, на котором
     * остался курсор
     * @return string Абсолютный путь
     */
    public function path(bool $file = false): string
    {
        $path = '';

        if ($file and $this->file) {
            $path = "{$this->core}/{$this->file}";
        } else {
            $path = $this->core;
        }

        unset($file);

        return $path;
    }

    /**
     * Получить список файлов внутри директории
     * 
     * @return array Список файлов
     */
    public function ls(): array
    {
        $content = [];
        $scan    = [];

        $scan = scandir($this->core);

        while (($i = array_search('.', $scan)) !== false) {
            unset($scan[$i]);
        }

        while (($i = array_search('..', $scan)) !== false) {
            unset($scan[$i]);
        }

        if ($scan) {
            $content = array_values($scan);
        }

        unset($scan, $i);

        return $content;
    }

    /**
     * Создать шаблон для преобразования методом DateTime::format()
     * 
     * @param string $string Строка содержащая спецсиволы
     * @return string Строка обернутая шаблоном для декодирования
     */
    public static function d(string $string): string
    {
        return self::$template['before'] . $string . self::$template['after'];
    }

    /**
     * Создание структуры директорий и/или файла
     * 
     * @param string $path Абсолютный путь, который необходимо реализовать
     * @param string $file (optional) Имя для файла
     * @param int $mode (optional) Права доступа (в восьмеричной системе
     * счисления)
     * @return string Абсолютный и развернутый путь к финальной папке/файлу
     * @throws Exception
     */
    public static function make(
        string $path,
        string $file = '',
        int    $mode = 0777
    ): string
    {
        $pattern = '';
        $npath   = '';

        $pattern = '/'
            . preg_quote(self::$template['before'])
            . '(.*)'
            . preg_quote(self::$template['after'])
            . '/ui';
        
        $npath = Pather::expath($path, ['rstrim' => true]);
        $npath = preg_replace_callback(
            $pattern, [__CLASS__, '_dateCB'], $npath
        );

        if (!file_exists($npath)) {
            if (!mkdir($npath, $mode, true)) {
                throw new Exception("Не удалось создать структуру пути: $path");
            }
        }

        if ($file) {
            $file  = preg_replace_callback(
                $pattern, [__CLASS__, '_dateCB'], $file
            );
            $npath = "$npath/$file";
            if (!file_exists($npath)) {
                if (file_put_contents($npath, '') === false) {
                    throw new Exception("Не удалось создать файл: $npath");
                }
            }
            if (!chmod($npath, $mode)) {
                throw new Exception(
                    "Не удалось установить права "
                    . sprintf('%04o', $mode)
                    . " файлу: $npath"
                );
            }
        }

        unset($path, $file, $mode, $pattern);

        return $npath;
    }

    /**
     * Создание структуры директорий и файла с контентом
     * 
     * @param string $path Абсолютный путь, который необходимо реализовать
     * @param string $content (optional) Текст, который необходимо записать
     * @param bool $append (optional) true - дописать в файл
     * @param int $mode (optional) Права доступа (в восьмеричной системе
     * счисления)
     * @return string Абсолютный и развернутый путь к файлу
     */
    public static function cmake(
        string  $path,
        ?string $content = null,
        bool    $append  = false,
        int     $mode    = 0777
    ): string
    {
        $file  = '';
        $quote = '';

        $path  = Pather::rstrim($path);
        $file  = basename($path);
        $quote = preg_quote($file);
        $path  = preg_replace("/$quote$/ui", '', $path);

        self::make($path, $file, $mode);

        $path = (new self($path))->file($file);

        if (is_string($content)) {
            $path = $path->content($content, $append);
        }

        $path = $path->path(true);

        unset($content, $append, $mode, $file, $quote);

        return $path;
    }

    /**
     * Callback декодирования шаблона функцией preg_replace_callback()
     * 
     * @param array $match То, что приходит из preg_replace_callback()
     * @return string Преобразованная строка
     */
    private static function _dateCB(array $match): string
    {
        $string = '';

        $string = $match[0];
        $string = str_replace(
            [
                self::$template['before'],
                self::$template['after'],
            ],
            '',
            $string
        );
        $string = (new DateTime())->format($string);

        unset($match);

        return $string;
    }

}
