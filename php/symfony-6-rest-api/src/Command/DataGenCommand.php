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

#[ AsCommand(
    name: 'data-gen',
    description: 'Generate init data',
) ]

class DataGenCommand extends Command {
    private ManagerRegistry $doctrine;
    private PositionRepository $positionRepository;

    public function __construct( PositionRepository $positionRepository, ManagerRegistry $doctrine ) {
        $this->doctrine = $doctrine;
        $this->PositionRepository = $positionRepository;
        parent::__construct();
    }

    protected function configure(): void {
        $this
        ->addArgument( 'arg1', InputArgument::OPTIONAL, 'Argument description' )
        ->addOption( 'option1', null, InputOption::VALUE_NONE, 'Option description' )
        ;
    }

    function insert( $parameters, $position, $entityManager ) {
        $position->setName( $parameters[ 'name' ] );
        $position->setSalary( $parameters[ 'salary' ] );
        $position->setCountry( $parameters[ 'country' ] );
        foreach ( $parameters[ 'skills' ] as $skill_name ) {
            $skills = new Skills();
            $skills->setName( $skill_name );
            $position->addSkill( $skills );
            $entityManager->persist( $skills );

        }
        $entityManager->persist( $position );
        $entityManager->flush();
        return $position;
    }

    function genData( $max ) {
        $entityManager = $this->doctrine->getManager();

        $nameArray = [ 'PHP', 'C++', 'JavaScript', 'HTML', 'NODE', '.NET', 'C#' ];
        $salaryArray = [ '40000', '50000', '60000', '70000', '80000', '90000', '100000' ];
        $countryArray = [ 'Spain', 'Germany', 'Argentina', 'Chile', 'USA', 'Canada', 'Cyprus' ];
        $skillsArray = [ 'php', 'c++', 'javascript', 'html', 'node', '.net', 'c#', 'docker', 'kubernetes', 'linux', 'mac', 'oracle', 'windows', 'mysql', 'mongodb' ];
        for ( $i = 1; $i<$max; $i++ ) {
            $nameIndex = rand( 0, count( $nameArray ) );
            $name = $nameArray[ $nameIndex ].'-'.$i;

            $countryIndex = rand( 0, count( $countryArray ) );
            $country = $countryArray[ $countryIndex ];

            $salaryIndex = rand( 0, count( $salaryArray ) );
            $salary = $salaryArray[ $salaryIndex ];
            $skills = array();
            for ( $j = 0; $j<rand( 0, count( $skillsArray ) );
            $j++ ) {
                $skills[ $j ] = $skillsArray[ rand( 0, (count( $skillsArray )-1) ) ];
            }
            $parameters = [ 'name'=>$name, 'salary'=>$salary, 'country'=>$country, 'skills'=> $skills  ];
            $position = new Position();
            $newPosition = $this->insert( $parameters, $position, $entityManager );
        }

    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {

        try {
            $io = new SymfonyStyle( $input, $output );
            $arg1 = $input->getArgument( 'arg1' );
            if ( !$arg1 ) {
                $io->note( 'Max amount of data mandantory' );
            }

            $this->genData( $arg1 );

            if ( $arg1 ) {
                $io->note( sprintf( 'You passed an argument: %s', $arg1 ) );
            }

            if ( $input->getOption( 'option1' ) ) {
                // ...
            }

            $io->success( 'You have a new command! Now make it your own! Pass --help to see your options.' );

            return Command::SUCCESS;
        } catch( Exception $e ) {
            $io->error( $e->message );

        }
    }
}
