<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\File\File;
use Grav\Common\Grav;
use RocketTheme\Toolbox\Event\Event;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BiblerefPlugin
 * @package Grav\Plugin
 */
class BiblerefPlugin extends Plugin
{
  /**
   * @return array
   *
   * The getSubscribedEvents() gives the core a list of events
   *     that the plugin wants to listen to. The key of each
   *     array section is the event that the plugin listens to
   *     and the value (in the form of an array) contains the
   *     callable (or function) as well as the priority. The
   *     higher the number the higher the priority.
   */
    public static function getSubscribedEvents()
    {
        return [
        'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

  /**
   * Initialize the plugin
   */
    public function onPluginsInitialized()
    {

        $this->copyInitData();


        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
        'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
        ]);
    }

  /**
   * Add current directory to twig lookup paths.
   */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }


  /**
   * Returns listing of available books from a yaml data file.
   *
   * @return array
   */
    public static function getBookOptions()
    {
        $options_array = array();
        $book_list_path = DATA_DIR . 'bibleref/book_data.yaml';
        $book_list_file = File::instance($book_list_path);
        if (!file_exists($book_list_path)) {
            BiblerefPlugin::copyInitData();
        }
        $sections = Yaml::parse($book_list_file->content());

        foreach ($sections as $section) {
            foreach ($section as $book_meta) {
                $options_array[$book_meta['book']['name']] = $book_meta['book']['name'];
            }
        }
        return $options_array;
    }

  /**
   * Returns listing of available chapters based on a selected book.
   *
   * @param string $book
   *  Book title to find number of chapters of.
   * @return array
   */
    public static function getBookChapterOptions($book_selected = "Psalms")
    {
        $max_chapter = 1;
        $options_array = array();
        $book_list_path = DATA_DIR . 'bibleref/book_data.yaml';
        $book_list_file = File::instance($book_list_path);
        if (!file_exists($book_list_path)) {
            BiblerefPlugin::copyInitData();
        }
        $sections = Yaml::parse($book_list_file->content());

        // Search each section for book.
        foreach ($sections as $section) {
            foreach ($section as $book_meta) {
                if ($book_meta['book']['name'] == $book_selected) {
                    $max_chapter = $book_meta['book']['chapters'];
                }
            }
        }

        for ($i = 1; $i <= $max_chapter; $i++) {
            $options_array[$i] = $i;
        }

        return $options_array;
    }

    /**
     * Copies initial book data to data directory.
     * 
     * @return type
     */
    public static function copyInitData()
    {
        $grav = new Grav();
        $init_data_file_path = __DIR__ . '/data/book_chapters.yaml';
        $user_data_file_path = DATA_DIR . '/bibleref/book_data.yaml';

        // No need to copy if file already exists.
        if (file_exists($user_data_file_path)) {
            return;
        }

        $init_file = File::instance($init_data_file_path);
        $user_file = File::instance($user_data_file_path);
        $data = Yaml::parse($init_file->content());
        $user_file->save(Yaml::dump($data));
        $grav::instance()['log']->notice('Copied bible book data to ' . DATA_DIR . 'bibleref/book_data.yaml');
    }
}
