<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Абстрактный класс шаблона
 */

abstract class Controller_Template extends Controller {

    /** @var string Основной шаблон */
    public $template = 'template';

    /** @var boolean Авторендеринг шаблона */
    public $auto_render = TRUE;

    /** @var string Название сайта */
    public $_site_name;

    /** @var string Заголовок страницы */
    public $title = NULL;

    /** @var string Символ разделяющий заголовок страницы и имя сайта */
    public $_title_separator;

    /** @var object Объект авторизации */
    protected $_auth;

    /** @var boolean Это ансинхронный запрос? */
    protected $_ajax = FALSE;

    /** @var boolean Это внутренний запрос? */
    protected $_internal = FALSE;

    /** @var Kohana_Config Конфигурация приложения */
    protected $_config;

    /** @var string Текущий ID страницы, по умолчанию - имя контролера */
    protected $_page_id;

    /** @var array Профилирование */
    protected $_benchmark;

    /** @var string Формат ответа на запрос */
    protected $_response_format;

    /**
     * Поддерживаемые форматы ответа для данного контролера
     * (accept-type => путь к шаблону формата)
     *
     * @var array
     */
    protected $_accept_formats = array(
        'text/html'             => '',
        'application/xhtml+xml' => '',
        'application/json'      => 'json',
        '*/*'                   => '', //ie7 ie8
    );

    /**
     * Метод before() всегда запускается перед любым экшеном
     *
     * @return void
     */
    public function before()
    {
        // Сначало выполнить родительский before()
        parent::before();

        // Проверка, является ли текущий запрос первым
        if (! $this->request->is_initial())
        {
            $this->_internal = TRUE;
            $this->auto_render = FALSE;
        }

        // Проверка, является ли текущий запрос асинхронным
        if ($this->request->is_ajax())
        {
            $this->_ajax = TRUE;
            $this->auto_render = FALSE;
        }

        // Получение конфигурации
        $this->_config = Kohana::$config->load('application');

        // Получение инстанса аутентификации
        //$this->_auth = Auth::instance();

        // Получение форматов ответа
        $accept_types = Request::accept_type();
        $accept_types = Arr::extract($accept_types, array_keys($this->_accept_formats));

        // Установка формата ответа по первому совпадению
        $this->_response_format = key($accept_types);

        if ($this->auto_render)
        {
            // Генерирует исключение если ожидаемый формат ответа не поддерживается
            if(! $accept_types = array_filter($accept_types))
            {
                throw new HTTP_Exception_415('Тип содержимого не поддерживается', NULL);
            }

            // Загрузка шаблона
            $this->template = View::factory($this->template);
            $this->_title_separator = $this->_config->get('title_separator', ' | ');

            // Установка переменных сайта
            $this->template
                ->set('site_name',      'ORB-UFO')
                ->set('site_slogan',    '')
                ->set('column_class',   '')
                ->set('head_title',     $this->title)
                ->set('title',          $this->title);

            // Установка мета тегов по умолчанию
            $this->_set_default_meta_tags();

            // Установка мета ссылок по умолнию
            $this->_set_default_meta_links();
        }
    }

    /**
     * Метод after() запускается после любого вызываемого экшена
     *
     * @return  void
     */
    public function after()
    {
        // Выполнить толко в том случае если это не AJAX
        if ($this->auto_render)
        {
            // Имя контролёря является ID текущей страницы
            // если ничего другого не определено
            empty($this->_page_id) AND $this->_page_id = $this->request->controller();

            // Некоторая магия для формирования класса страницы
            $classes = array();
            $classes[] = $this->request->controller();
            $classes[] = $this->request->action();
            //$classes[] = ($this->_auth->logged_in()) ? 'logged-in' : 'not-logged-in';
            $page_class = implode(' ', array_unique(array_map('trim', $classes)));

            // Установка заголовка страницы
            $this->_set_head_title();

            // Первичное меню
            //$primary_menu = Menu::links('main-menu', array('class' => 'menus nav'));

            $this->template
                //->set('lang',         Cerber::$lang)    // Текущий язык страницы
                //->set('page_id',      $this->_page_id)  // Текущий ID страницы
                //->set('page_class',   $page_class)      // Текущий класс страницы
                //->set('primary_menu', $primary_menu)    // Первичное меню
                ->set('title',        $this->title)     // Текущий заголовок страницы
                ->set('content',      $this->content);

            // Установка заголовков сервера по умолчанию
            $this->_set_default_server_headers();

            // Вывод шаблона в ответе
            $this->response->body($this->template);
        }
        // Если это AJAX запрос
        elseif( $this->_ajax === TRUE )
        {
            // Установка заголовков сервера по умолчанию
            $this->_set_default_server_headers();
            // Подготовка ответа
            $output = $this->response->body();

            // Кодирование ответа
            if($this->_response_format === 'application/json')
            {
                $output = JSON::encode($output);
            }

            // Вывод ответа
            $this->response->body($output);
        }
        // Если это внутренний запрос
        elseif( $this->_internal === TRUE )
        {
            // Установка заголовков сервера по умолчанию
            $this->_set_default_server_headers();
            // Подготовка ответа
            $output = $this->response->body();
            // Вывод ответа
            $this->response->body( $output );
        }

        // Выполнить родительский after()
        parent::after();
    }

    /**
     * Установка заголовка страницы
     *
     * @return void
     */
    protected function _set_head_title()
    {
        if ($this->title)
        {
            $head_title = array(strip_tags($this->title), $this->template->site_name);
        }
        else
        {
            $head_title = array($this->template->site_name);
            if ($this->template->site_slogan)
            {
                $head_title[] = $this->template->site_slogan;
            }
        }

        $this->template->head_title = implode($this->_title_separator, $head_title);
    }

    /**
     * Установка заголовков сервера по умолчанию
     *
     * @return  void
     */
    protected function _set_default_server_headers()
    {
        $headers['Content-Type'] = $this->_response_format.'; charset='.Kohana::$charset;
        $headers['X-Powered-By'] = 'ORB-UFO';
        $headers['X-ORB-Version'] = '1.0.0.3 (synapsis)';

        $this->_set_server_headers($headers);
    }

    /**
     * Установка заголовков сервера
     *
     * @param   array Ассоциативный массив заголовков сервера
     * @return  void
     */
    protected function _set_server_headers($headers)
    {
        if (is_array($headers) AND ! empty($headers))
        {
            $this->response->headers($headers);
        }
    }

    /**
     * Установка мета тегов по умолчанию
     *
     * Используется конфигурация
     *
     * @return  void
     *
     * @uses    Meta::tags  Обертка для мета тегов
     */
    protected function _set_default_meta_tags()
    {
        $meta = $this->_config->get('meta', array());
        $tags = Arr::get($meta, 'tags');

        if ($tags)
        {
            foreach ($tags as $name => $value)
            {
                $conditional = NULL;
                if (is_array($value))
                {
                    $conditional = Arr::get($value, 'conditional');
                    $value = Arr::get($value, 'value', '');
                }
                $attributes = array();
                if (isset($conditional))
                {
                    $attributes['conditional'] = $conditional;
                }
                Meta::tags($name, $value, $attributes);
            }
        }
    }

    /**
     * Установка мета ссылок по умолчанию
     *
     * Используется конфигурация
     *
     * @return void
     *
     * @uses    Meta::tags  Обертка для мета ссылок
     */
    protected function _set_default_meta_links()
    {
        $meta = $this->_config->get('meta', array());
        $links = Arr::get($meta, 'links');

        if ($links)
        {
            foreach ($links as $url => $attrs)
            {
                Meta::links($url, $attrs);
            }
        }
    }

}
