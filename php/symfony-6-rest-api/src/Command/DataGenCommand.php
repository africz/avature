<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Position;
use App\Entity\Skills;
use App\Repository\PositionRepository;
use Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\KernelInterface;

#[ AsCommand(
    name: 'data-gen',
    description: 'Generate init data',
) ]

class DataGenCommand extends Command {
    private ManagerRegistry $doctrine;
    private PositionRepository $positionRepository;
    private $entityManager;
    private int $generatedData = 0;
    private string $projectRoot;

    public function __construct( PositionRepository $positionRepository, ManagerRegistry $doctrine, KernelInterface $appKernel ) {
        $this->doctrine = $doctrine;
        $this->PositionRepository = $positionRepository;
        $this->entityManager = $this->doctrine->getManager();
        $this->projectRoot = $appKernel->getProjectDir();
        //unlink($this->projectRoot.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'data.db');
        parent::__construct();
    }

    protected function configure(): void {
        $this
        ->addArgument( 'arg1', InputArgument::OPTIONAL, 'Argument description' )
        ->addOption( 'option1', null, InputOption::VALUE_NONE, 'Option description' )
        ;
    }

    function insert( $parameters, $position ) {
        $position->setName( $parameters[ 'name' ] );
        $position->setSalary( $parameters[ 'salary' ] );
        $position->setCountry( $parameters[ 'country' ] );
        foreach ( $parameters[ 'skills' ] as $skill_name ) {
            $skills = new Skills();
            $skills->setName( $skill_name );
            $position->addSkill( $skills );
            $this->entityManager->persist( $skills );

        }
        $this->entityManager->persist( $position );
        $this->entityManager->flush();
        return $position;
    }

    function create() {
        $conn = $this->entityManager->getConnection();
        $sql = 'DROP TABLE IF EXISTS doctrine_migration_versions;'.PHP_EOL;
        $sql .= 'DROP TABLE IF EXISTS position;'.PHP_EOL;
        $sql .= 'DROP TABLE IF EXISTS sqlite_sequence;'.PHP_EOL;
        $sql .= 'DROP TABLE IF EXISTS skills;'.PHP_EOL;
        $sql .= 'CREATE TABLE doctrine_migration_versions (version VARCHAR(191) NOT NULL, executed_at DATETIME DEFAULT NULL, execution_time INTEGER DEFAULT NULL, PRIMARY KEY(version));'.PHP_EOL;
        $sql .= 'CREATE TABLE position (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(40) NOT NULL, salary INTEGER NOT NULL, country VARCHAR(40) NOT NULL);'.PHP_EOL;
        $sql .= 'CREATE TABLE sqlite_sequence(name,seq);'.PHP_EOL;
        $sql .= 'CREATE TABLE skills (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position_id INTEGER NOT NULL, name VARCHAR(40) NOT NULL, CONSTRAINT FK_D5311670DD842E46 FOREIGN KEY (position_id) REFERENCES position (id) NOT DEFERRABLE INITIALLY IMMEDIATE);'.PHP_EOL;
        $sql .= 'CREATE INDEX IDX_D5311670DD842E46 ON skills (position_id);'.PHP_EOL;
        $sql .= 'CREATE INDEX skills_name_IDX ON skills (name,position_id);'.PHP_EOL;
        $sql .= 'CREATE INDEX position_country_IDX ON "position" (country);'.PHP_EOL;
        $sql .= 'CREATE INDEX position_salary_IDX ON "position" (salary);'.PHP_EOL;
        $sql .= 'CREATE INDEX position_name_IDX ON "position" (name);'.PHP_EOL;

        $statement = $conn->prepare( $sql );
        $statement->execute();
    }

    function init() {
        $this->create();
    }

    function genData( $max ) {
        $nameArray = [ 'PHP', 'C++', 'JavaScript', 'HTML', 'NODE', '.NET', 'C#' ];
        $salaryArray = [ '40000', '50000', '60000', '70000', '80000', '90000', '100000' ];
        $countryArray = [ 'Spain', 'Germany', 'Argentina', 'Chile', 'USA', 'Canada', 'Cyprus' ];
        $skillsArray = [ 'php', 'c++', 'javascript', 'html', 'node', '.net', 'c#', 'docker', 'kubernetes', 'linux', 'mac', 'oracle', 'windows', 'mysql', 'mongodb' ];
        for ( $i = 0; $i<$max; $i++ ) {
            $nameIndex = rand( 0, ( count( $nameArray )-1 ) );
            $name = $nameArray[ $nameIndex ].'-'.( $i+1 );

            $countryIndex = rand( 0, ( count( $countryArray )-1 ) );
            $country = $countryArray[ $countryIndex ];

            $salaryIndex = rand( 0, ( count( $salaryArray )-1 ) );
            $salary = $salaryArray[ $salaryIndex ];
            $skills = array();
            for ( $j = 0; $j<rand( 0, ( count( $skillsArray )-1 ) );
            $j++ ) {
                $skills[ $j ] = $skillsArray[ rand( 0, ( count( $skillsArray )-1 ) ) ];
            }
            $parameters = [ 'name'=>$name, 'salary'=>$salary, 'country'=>$country, 'skills'=> $skills ];
            $position = new Position();
            $newPosition = $this->insert( $parameters, $position );
            echo ( $i.'/'.$max ).PHP_EOL;
            $this->generatedData++;
        }

    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {

        try {
            $io = new SymfonyStyle( $input, $output );
            $arg1 = $input->getArgument( 'arg1' );
            if ( !$arg1 ) {
                $io->error( 'Max amount of data mandantory as 1st parameter!' );
            }
            if ( $arg1 ) {
                $io->note( sprintf( 'You passed an argument: %s', $arg1 ) );
            }

            $this->init();
            $this->genData( $arg1 );

            if ( $input->getOption( 'option1' ) ) {
                // ...
            }

            $io->success( $this->generatedData.'/'.$arg1.' records generated successfully.' );

            return Command::SUCCESS;
        } catch( Exception $e ) {
            echo $e->getMessage();
            return Command::FAILURE;
        }
    }
}
