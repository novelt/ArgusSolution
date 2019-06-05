<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 01/11/2016
 * Time: 10:31
 */

namespace AppBundle\Tests;

use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Services\Exception\DatabaseException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BaseKernelTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $em;

    /**
     * @var string
     */
    protected $rootDir;


    /**
     * @var string
     */
    protected $testDir;

    protected function setUp()
    {
        self::bootKernel();

        $this->container = static::$kernel->getContainer();

        $this->rootDir = static::$kernel->getRootDir();
        $this->testDir = $this->rootDir.'\\..\\src\\AppBundle\\Tests\\';

        $this->em = $this->container
            ->get('doctrine')
            ->getManager();
    }

    protected function initializeDatabase() {
        $this->dropDatabaseSchema();
        $this->createDatabaseSchema();
    }

    protected function getService($serviceName) {
        return $this->container->get($serviceName);
    }

    protected function getParameter($paramName) {
        return $this->container->getParameter($paramName);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokePrivateMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function assertIsArray($object, $message=null) {
        $this->assertTrue(is_array($object),  $message);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function dropDatabaseSchema() {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:drop',
            '--env' => "test",
            '--full-database' => true,
            '--force' => true,
        ));

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();

        if (strpos($content, 'Database schema dropped successfully') === false) {
            throw new \Exception(sprintf("An error occurred when trying to drop the database schema: %s", $content));
        }

        // return new Response(""), if you used NullOutput()
        return $content;
    }

    protected function createDatabaseSchema() {
        $initDatabaseSQLFiles = $this->getInitDatabaseSQLFiles();

        foreach($initDatabaseSQLFiles as $initDatabaseSQLFile) {
            $this->runSQLFile($initDatabaseSQLFile);
        }
    }

    /**
     * Run the given file name.
     * @param $filename
     * @throws DatabaseException
     */
    protected function runSQLFile($filename)
    {
        $database_driver = $this->getParameter('database_driver');

        //WARNING: hard coded for MySQL. We have issues when running stored procedures containing DELIMITER SQL
        if($database_driver == "pdo_mysql") {
            $MySQLPath = $this->getParameter('unit_test_mysql_path');
            $database_name = $this->getParameter('database_name');
            $database_port = $this->getParameter('database_port');
            $database_username = $this->getParameter('database_user');
            $database_password = $this->getParameter('database_password');

            if($filename !== null) {
                $cmd='"'.$MySQLPath.'" --database='.$database_name.' --user='.$database_username.' --password='.$database_password.' --port='.$database_port.' < "'.$filename.'" 2>&1';

                $exitCode=-1;
                exec($cmd,$output,$exitCode);

                if ($exitCode!=0) {
                    $errorMessage = '';

                    if(!empty($output)) {
                        $errorMessage = implode(". ", $output);
                    }

                    throw new DatabaseException(sprintf("Error when running the file '%s'. %s", $filename, $errorMessage));
                }
            }
        }
        else {
            throw new DatabaseException("The driver '%s' is not handled by the unit test database initialization", $database_driver);
        }
    }

    protected function getInitDatabaseSQLFiles()
    {
        $SQLFiles = [];

        $folders = $this->getParameter("unit_test_init_db_folders");

        //To do later, would be to recursively get SQL files in sub folders
        foreach ($folders as $folder) {
            $files = scandir($folder);
            natsort($files);

            foreach($files as $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if(!empty($ext) && strtoupper($ext) == 'SQL') {
                    $SQLFiles[] = $folder.$file;
                }
            }
        }

        return $SQLFiles;
    }


    /**
     * @param $content
     * @return UploadedFile
     * @throws \Exception
     */
    protected function createUploadedFile($content) {
        $tmpfname = tempnam(sys_get_temp_dir(), 'uploadedfile');
        if ($tmpfname === false) {
            throw new \Exception('File can not be opened.');
        }

        // Put content in this file
        $file = fopen($tmpfname, "w");
        $path = stream_get_meta_data($file)['uri'];
        file_put_contents($path, $content);

        return new UploadedFile($path, "test.txt", null, null, null, true);
    }

    /**
     * Returns a mock token storage to simulate many users and permissions.
     * @param $isAdmin boolean
     * @param array $userFunctionsToMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockTokenStorage($username=null, $usernameCanonical=null, $email=null, $emailCanonical=null, $enabled=null, $lastLogin=null, $firstName=null, $lastName=null, $isAdmin=null, $locale=null, SesDashboardSite $site=null, array $userFunctionsToMock = []) {
        $tokenStorage = $this->getMockBuilder(TokenStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user  = $this->getMockBuilder(SesDashboardUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage->method("getToken")->willReturn($token);
        $token->method("getUser")->willReturn($user);

        if($username !== null) $user->method("getUsername")->willReturn($username);

        if($usernameCanonical !== null) $user->method("getUsernameCanonical")->willReturn($usernameCanonical);
        if($email !== null) $user->method("getEmail")->willReturn($email);
        if($emailCanonical !== null) $user->method("getEmailCanonical")->willReturn($emailCanonical);
        if($enabled !== null) $user->method("isEnabled")->willReturn($enabled);
        if($lastLogin !== null) $user->method("getLastLogin")->willReturn($lastLogin);
        if($firstName !== null) $user->method("getFirstName")->willReturn($firstName);
        if($lastName !== null) $user->method("getLastName")->willReturn($lastName);
        if($isAdmin !== null) $user->method("isAdmin")->willReturn($isAdmin);
        if($locale !== null) $user->method("getLocale")->willReturn($locale);
        if($site !== null) $user->method("getSite")->willReturn($site);

        foreach ($userFunctionsToMock as $functionName => $return) {
            $user->method("$functionName")->willReturn($return);
        }

        static::$kernel->getContainer()->get('security.token_storage')->setToken($token);

        return $tokenStorage;
    }
}