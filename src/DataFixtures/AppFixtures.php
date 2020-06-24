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
                ->setEmail($faker->safeEmail())
                ->setPassword($this->encoder->encodePassword($member, "password"))
                ->setMoney($faker->numberBetween(-1000, 2500))
                ->setAddress($faker->address());

            $manager->persist($member);
        }

        // On génère 20 types de services
        for ($i = 0; $i < 20; $i++) {
            $dutyType = new DutyType();

            $dutyType
                ->setTitle($faker->sentence(3))
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
            if ($i % 5 != 0) {
                $duty
                    ->setAskerValidAt($faker->dateTimeBetween($duty->getCreatedAt(), "-1 week", "Europe/Paris"));
            }
            // S'il y a l'offerer c'est qu'on est passé par cette étapes
            if ($duty->getAskerValidAt() && $duty->getOfferer()) {
                $duty
                    ->setOffererValidAt($faker->dateTimeBetween($duty->getAskerValidAt(), "-1 week", "Europe/Paris"));
            }
            // Les 5/6 du temps
            if ($i % 6 != 0) {
                if ($duty->getOfferer()) {
                    $duty
                        ->setDoneAt($faker->dateTimeBetween($duty->getOffererValidAt(), "-1 week", "Europe/Paris"));
                } else {
                    $duty
                        ->setDoneAt($faker->dateTimeBetween($duty->getCreatedAt(), "-1 week", "Europe/Paris"));
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
                ->setDuration($faker->randomDigit())
                ->setPlace($places[rand(0, count($places) - 1)]);

            if ($duty->getSetbackAt()) {
                $duty
                    ->setStatus("setback");
            } elseif ($duty->getDoneAt() < new DateTime()) {
                $duty
                    ->setStatus("finished");
            } elseif ($duty->getOffererValidAt()) {
                $duty
                    ->setStatus("offerer validation");
            } elseif ($duty->getAskerValidAt()) {
                $duty
                    ->setStatus("asker validation");
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

            $manager->persist($conversation);

            // Générer des messages par conversation
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
                $message
                    ->setContent($faker->sentences(3, true))
                    ->setCreatedAt($faker->dateTimeBetween($conversation->getCreatedAt(), 'now', "Europe/Paris"));

                $manager->persist($message);

            }

        }

        $types = array("Message","Validation","Avertissement","Mise en retrait");
        // On génère 200 notifications
        for ($i=0; $i < 200; $i++) { 
            $notification = new Notification();

            $notification
                ->setMember($members[rand(0, count($members)-1)])
                ->setContent($faker->sentence())
                ->setCreatedAt($faker->dateTimeBetween("-1 year"))
                ->setIsRead($faker->boolean(60));
                // ->setType($types[rand(0, count($types)-1)]);

            $manager->persist($notification);
        }

        $manager->flush();
    }
}

// SELECT m.name, m.firstname, m.id, SUM(d.price) as higher, MAX(d.done_at) as total
// FROM member as m
// LEFT JOIN duty as d on m.id=d.offerer_id
// WHERE d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)
// AND d.done_at is not NULL
// ORDER BY higher DESC