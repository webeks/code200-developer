<?php namespace Code200\Developer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Dbbackup extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'code200:dbbck';

    /**
     * @var string The console command description.
     */
    protected $description = 'create DB backup';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $databaseConfigPrefix = 'database.connections.'.Config::get('database.default');
        $database = Config::get($databaseConfigPrefix.'.database');
        $username = Config::get($databaseConfigPrefix.'.username');
        $password = Config::get($databaseConfigPrefix.'.password');

        $storageDir = storage_path("backup/db");
        if(!is_dir($storageDir)){
            mkdir($storageDir, 0755, true);
            $this->info("Created backup dir: " . $storageDir);
        }

        switch ( config("database.default") ) {
            case "mysql":
                $command = "mysqldump -u $username --password=$password $database > {$storageDir}/{$database}_".date("Ymd_his", time()).".sql";
                $this->info("Executing mysqldump backup in: {$storageDir}/{$database}_".date("Ymd_his", time()).".sql");
                exec($command);

                $this->info("MySql dump finished.");
                break;
            default:
                $this->comment('This only works for MySql database.');
                $this->comment('No export was made !!!');
        }
    }

    private function createDirIfDoesntExist($dir) {

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
