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

/****** Sélection des 5 membres les plus aidants *******/
/*
SELECT m.name, m.firstname, m.id, 
(SELECT SUM(d.price) FROM duty as d WHERE offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
(SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id) as last_duty
FROM member as m
ORDER BY higher DESC
LIMIT 5
*/

/****** Sélection des 5 membres les plus aidés *******/
/*
SELECT m.name, m.firstname, m.id, 
(SELECT SUM(d.price) FROM duty as d WHERE asker_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
(SELECT MAX(du.done_at) FROM duty as du WHERE du.asker_id=m.id) as last_duty
FROM member as m  
ORDER BY `higher`  DESC
LIMIT 5
*/

/****** Sélection des 20 derniers services rendus *******/
/*
SELECT d.id, concat(askM.firstname, ' ', askM.name) as asker, concat(offM.firstname, ' ', offM.name) as offerer, dt.title as type, d.created_at, d.done_at, d.price
FROM duty as d
LEFT JOIN member as askM on d.asker_id=askM.id
LEFT JOIN member as offM on d.offerer_id=offM.id
LEFT JOIN duty_type as dt on d.duty_type_id=dt.id
WHERE d.status = "finished"
ORDER BY d.done_at DESC
LIMIT 20
*/

/****** Sélection des types d'activités *******/
/*
SELECT dt.id, dt.title, dt.hourly_price, 
(select count(d.id) from duty as d where d.duty_type_id = dt.id) as howMany,
(select sum(du.price) from duty as du where du.duty_type_id = dt.id) as saltAmount
FROM duty_type as dt
*/

/****** Sélection des volumes d'échange *******/
// /!\ Il faudra ici faire une boucle et les date de début et de fin de semaine seront données à chaque tour de boucle //
/*
        ## Version exemple avec des dates données ##
SELECT
(select sum(d1.price) from duty as d1 where d1.status = "finished" and d1.done_at between "2020-04-18 17:08:48" AND "2020-05-01 03:04:09") as saltAmount,
(select count(d2.id) from duty as d2 where d2.status = "finished" and d2.done_at between "2020-04-18 17:08:48" AND "2020-05-01 03:04:09") as dutiesAmount
FROM duty as d
LIMIT 1

        ## Version qu'il faudra intégrer ##
SELECT
(select sum(d1.price) from duty as d1 where d1.status = "finished" and d1.done_at between :weekStart AND :weekEnd) as saltAmount,
(select count(d2.id) from duty as d2 where d2.status = "finished" and d2.done_at between :weekStart AND :weekEnd) as dutiesAmount
FROM duty as d
LIMIT 1

*/