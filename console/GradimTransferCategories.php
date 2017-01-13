<?php namespace Code200\Developer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;
use October\Rain\Database\ModelException;
use PDO;
use RainLab\Blog\Models\Category;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GradimTransferCategories extends Command
{

    const OLD_DB = "gradim_migration";
    const OLD_DB_USER = "root";
    const OLD_DB_PASS = "";

    /**
     * @var string The console command name.
     */
    protected $name = 'gradim:transfer-categories';

    /**
     * @var string The console command description.
     */
    protected $description = 'Transfers categories from old Joomla to OctoberCMS';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        //connect to odl db connection
        $pdo = new PDO(
            'mysql:host=localhost;dbname=' . self::OLD_DB . ';charset=utf8',
            self::OLD_DB_USER,
            self::OLD_DB_PASS
        );

        $sections = $pdo->query("select * from jos_sections");
        try {
//            Category::where('id', '!=', 1)->delete();

            foreach ($sections as $section) {
//            print_r($section);

                $this->info("Importing section: " . $section['alias']);

                $newCategory = new Category();
                $newCategory->name = $section['title'];
                $newCategory->slug = $section['alias'];
                $newCategory->code = "s" . $section['id'];
                $newCategory->save();
                $parentId = $newCategory->id;


                $categories = $pdo->query("SELECT * FROM jos_categories WHERE section = {$section['id']}");
                foreach ($categories as $cat) {
                    $this->info("Importing category: " . $cat['title'] . " " . $cat['alias']);


                    $newCategory = new Category();
                    $newCategory->name = $cat['title'];
                    $newCategory->slug = $cat['alias'];
                    $newCategory->parent_id = $parentId;
                    $newCategory->code = "c" . $cat['id'];
                    $newCategory->save();


                }
            }
        } catch (ModelException $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
        $this->output->writeln('Categories imported!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
