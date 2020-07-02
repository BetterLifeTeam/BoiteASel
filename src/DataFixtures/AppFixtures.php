<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Duty;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\DutyType;
use App\Entity\Conversation;
use App\Entity\Notification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        // On instancie l'entité faker
        $faker = Factory::create('fr_FR');

        // On génère 50 membres dans l'application
        for ($i = 0; $i < 50; $i++) {
            $member = new Member();

            $memberRoles = array();
            $roleProb = rand(0, 100);
            if ($roleProb >= 95) {
                $memberRoles = array("ROLE_MEMBER", "ROLE_ADMIN", "ROLE_SUPER_ADMIN");
            } elseif ($roleProb >= 85) {
                $memberRoles = array("ROLE_MEMBER", "ROLE_ADMIN");
            } else {
                $memberRoles = array("ROLE_MEMBER");
            }

            $member
                ->setName($faker->lastName())
                ->setFirstname($faker->firstName())
                ->setRoles($memberRoles)
                ->setEmail($member->getFirstname().".".$member->getName()."@".$faker->freeEmailDomain())
                ->setPassword($this->encoder->encodePassword($member, "password"))
                ->setMoney($faker->numberBetween(-1000, 2500))
                ->setAddress($faker->address());

            $manager->persist($member);
        }

        $types = array('Cours à domicile', 'Ménage', 'Déménagement', 'Garde d\'animaux', 'Garde d\'enfants', 'Soutien scolaire',
        'Couture', 'Proderie', 'Coaching sportif', 'Coaching personnel', 'Mécanicien', 'Bricolage', 'Jardinnage', 'Covoiturage',
        'Aide à la personne', 'Dépannage informatique', 'Cuisine', 'Evenement', 'Livraison de courses', 'Personnal Shopper');

        // On génère 20 types de services
        for ($i = 0; $i < 20; $i++) {
            $dutyType = new DutyType();

            $dutyType
                ->setTitle($types[$i])
                ->setHourlyPrice($faker->numberBetween(100, 300))
                ->setStatus("actif");

            $manager->persist($dutyType);
        }

        $manager->flush();


        // On récupère les Repository et toutes les instances des 2 entités
        $memberRepo = $manager->getRepository(Member::class);
        $dutyTypeRepo = $manager->getRepository(DutyType::class);

        $members = $memberRepo->findAll();
        $dutyTypes = $dutyTypeRepo->findAll();

        // On génère 100 demandes de service
        for ($i = 0; $i < 100; $i++) {
            // if (is_array($members)) {
            //     # code...
            // }
            $duty = new Duty();

            $duty
                ->setDutyType($dutyTypes[rand(0, count($dutyTypes) - 1)])
                ->setAsker($members[rand(0, count($members) - 1)]);
            //Les 3/4 du temps
            if ($i % 4 != 0) {
                $offererId = rand(0, count($members) - 1);
                // Si c'est le même que l'asker on ajoute 1
                if ($offererId == $duty->getAsker()->getId()) {
                    $duty
                        ->setOfferer($members[$offererId + 1]);
                } else {
                    $duty
                        ->setOfferer($members[$offererId]);
                }
            }

            $duty
                ->setTitle($faker->sentence(3))
                ->setDescription($faker->paragraph())
                ->setCreatedAt($faker->dateTimeBetween("-1 years", "-1 week", "Europe/Paris"));
            // Les 2/3 du temps
            if ($i % 3 != 0) {
                $duty
                    ->setCheckedAt($faker->dateTimeBetween($duty->getCreatedAt(), "-1 week", "Europe/Paris"));
            }
            if ($i % 10 != 0) {
                $duty
                    ->setAskerValidAt($faker->dateTimeBetween($duty->getCreatedAt(), "-1 week", "Europe/Paris"));
            }
            // S'il y a l'offerer c'est qu'on est passé par cette étapes
            if ($duty->getAskerValidAt() && $duty->getOfferer()) {
                $duty
                    ->setOffererValidAt($faker->dateTimeBetween($duty->getAskerValidAt(), "-1 week", "Europe/Paris"));
            }
            // Les 5/6 du temps
            if ($i % 8 != 0) {
                if ($duty->getOfferer()) {
                    $duty
                        ->setDoneAt($faker->dateTimeBetween($duty->getOffererValidAt(), "now", "Europe/Paris"));
                } else {
                    $duty
                        ->setDoneAt($faker->dateTimeBetween($duty->getCreatedAt(), "now", "Europe/Paris"));
                }
            }
            // Les 1/5 du temps
            if ($i % 10 == 0) {
                $duty
                    ->setSetbackAt($faker->dateTimeBetween($duty->getCreatedAt(), "-1 week", "Europe/Paris"));
            }
            //Les différents endroits possibles
            $places = array("dans le jardin", "dans la maison", "ailleurs en extérieur", "ailleurs en intérieur", "autre");
            $duty
                ->setDuration($faker->randomDigitNot(0))
                ->setPlace($places[rand(0, count($places) - 1)]);

            if ($duty->getSetbackAt()) {
                $duty
                    ->setStatus("setback");
            } elseif ($duty->getAskerValidAt() && $duty->getOffererValidAt() && $duty->getDoneAt() && $duty->getDoneAt() < new DateTime()) {
                $duty
                    ->setStatus("finished");
            } elseif ($duty->getOffererValidAt()) {
                $duty
                    ->setStatus("offerer_validation");
            } elseif ($duty->getAskerValidAt()) {
                $duty
                    ->setStatus("asker_validation");
            } elseif ($duty->getCheckedAt()) {
                $duty
                    ->setStatus("checked");
            } else {
                $duty
                    ->setStatus("not checked");
            }

            $duty
                ->setPrice($duty->getDuration() * $manager->getRepository(DutyType::class)->find($duty->getDutyType())->getHourlyPrice());

            $manager->persist($duty);
        }

        $manager->flush();

        $dutyRepo = $manager->getRepository(Duty::class);

        $duties = $dutyRepo->findAll();
        //création de 25 conversations
        for ($i = 0; $i < 25; $i++) {
            $conversation = new Conversation();

            // Les 1/5 du temps
            if ($i % 5 == 0) {
                $conversation
                    ->setDuty($duties[rand(0, count($duties)-1)]);
            }
            $conversation
                ->setMember1($members[rand(0, count($members)-1)]);

            $member2Id = rand(0, count($members)-1);

            if ($member2Id == $conversation->getMember1()->getId()) {
                $conversation
                    ->setMember2($members[$member2Id + 1]);
            } else {
                $conversation
                    ->setMember2($members[$member2Id]);
            }
            if ($conversation->getDuty()) {
                $conversation
                    ->setCreatedAt($faker->dateTimeBetween($conversation->getDuty()->getCreatedAt(), "-1 week", "Europe/Paris"));
            } else {
                $conversation
                    ->setCreatedAt($faker->dateTimeBetween("-1 year", "-1 week", "Europe/Paris"));
            }

           

            // Générer des messages par conversation
            $prevDate = "";
            for ($j=0; $j < rand(10, 30); $j++) { 
                $message = new Message();

                $message
                    ->setConversation($conversation);
                $senderId = rand(1,2);
                if ($senderId == 1) {
                    $message
                        ->setSender($conversation->getMember1());
                } else {
                    $message
                        ->setSender($conversation->getMember2());
                }
                $date = $faker->dateTimeBetween($conversation->getCreatedAt(), 'now', "Europe/Paris");
                $message
                    ->setContent($faker->sentences(3, true))
                    ->setCreatedAt($date);

                $manager->persist($message);
                
                if ($date > $prevDate) {
                    $conversation->setLastActivity($date);
                }

                $prevDate = $date;

            }

            $manager->persist($conversation);

        }

        // On génère 150 notifications
        for ($i=0; $i < 150; $i++) { 
            $notification = new Notification();

            $notification
                ->setMember($members[rand(0, count($members)-1)])
                ->setContent($faker->sentence())
                ->setCreatedAt($faker->dateTimeBetween("-1 year"))
                ->setIsRead($faker->boolean(60))
                ->setType("divers");

            $manager->persist($notification);
        }

        $manager->flush();
    }
}