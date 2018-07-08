<?php

namespace Hellpers;

use \Exception;

/**
 * Создание/воссоздание структуры пути
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
     * Инициализация
     *
     * @param string $core Абсолютный путь к корню приложения
     * @throws Exception
     */
    public function __construct($core = '')
    {
        $this->core = $core;
        unset($core);

        // Валидация корня приложения
        if (!is_string($this->core)) {
            throw new Exception(Dencoder::utf8('Корень приложения (параметр "core") должен быть строкой.'));
        } else {
            if (!realpath($this->core) or !is_dir(realpath($this->core))) {
                throw new Exception(Dencoder::utf8('Некорректный адрес приложения: "' . $this->core . '".'));
            } else {
                $this->core = realpath($this->core);
            }
        }

        if ($this->core != '/') {
            $this->core = preg_replace('/\/+$/ui', '', $this->core);
        }
    }

    /**
     * Получение, проверка и создание пути
     * @param string $path Внутренний путь к дирректории сохранения логов.
     * В качестве разделителя директорий используется - "/".
     * "\" - Используется для экранирования, т.к. присутствует поддержка спецсимволов PHP-функции date().
     * Обратные ссылки ".." - будут проигнорированы.
     * @param string $file Имя файла (при необходимости создать файл). Присутствует поддержка спецсимволов PHP-функции date().
     * @return string Абсолютный путь
     * @throws Exception
     */
    public function get($path = '', $file = '')
    {
        // Валидация внутреннего пути
        if (!is_string($path)) {
            throw new Exception(Dencoder::utf8('Внутренний путь (параметр "$path") должен быть строкой.'));
        }

        // Валидация имени файла
        if (!is_string($file)) {
            throw new Exception(Dencoder::utf8('Имя файла (параметр "$file") должно быть строкой.'));
        }

        // Обрезаем слеши в начале и конце пути
        $path = preg_replace(['/^\/+/ui', '/\/+$/ui'], '', $path);

        // Создание структуры директорий
        if (!file_exists($this->core . '/' . $path)) {
            $structure = explode('/', $path);
            $path = '';
            foreach ($structure as $folder) {
                $folder = Dencoder::name(date($folder));
                if (empty($folder) or $folder == '..' or $folder == '.') {
                    unset($folder);
                    continue;
                }
                $path .= '/' . $folder;
                $path = preg_replace(['/^\/+/ui', '/\/+$/ui'], '', $path);
                if (!file_exists($this->core . '/' . $path)) {
                    if (!mkdir($this->core . '/' . $path)) {
                        throw new Exception(Dencoder::utf8('Не удалось создать папку: "' . $this->core . '/' . $path . '".'));
                    }
                }
                unset($folder);
            }
        }
        unset($structure);
        $path = $this->core . '/' . $path;

        // Создание файла
        if (!empty(trim($file))) {
            $path = $path . '/' . Dencoder::name(date($file));
            if (!file_exists($path)) {
                $fp = fopen($path, 'ab');
                if (!$fp) {
                    throw new Exception(Dencoder::utf8('Не удалось создать файл: "' . $path . '".'));
                }
                fclose($fp);
                unset($fp);
            }
        }
        unset($file);
        return $path;
    }

    /**
     * Экраинирование каждой буквы
     * @param string $string Строка
     * @return string Строка, где каждая буква экранирована
     */
    public function q($string)
    {
        if (!is_string($string)) {
            throw new Exception(Dencoder::utf8('Параметр "$string" должен быть строкой.'));
        }
        return Dencoder::quote($string);
    }
}