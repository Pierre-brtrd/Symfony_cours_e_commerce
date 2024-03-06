<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Taxe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('pierre@test.com')
            ->setFirstName('Pierre')
            ->setLastName('Bertrand')
            ->setPassword(
                $this->hasher->hashPassword(
                    new User(),
                    'Test1234!'
                )
            )
            ->setRoles(['ROLE_ADMIN'])
            ->setEnable(true);

        $manager->persist($user);

        $tva = (new Taxe)
            ->setName('TVA 20%')
            ->setRate(0.20)
            ->setEnable(true);

        $manager->persist($tva);

        for ($i = 0; $i < 10; ++$i) {
            $product = (new Product)
                ->setTitle($this->faker->unique()->word())
                ->setDescription(file_get_contents('https://loripsum.net/api/3/short/link/ul'))
                ->setTaxe($tva)
                ->setPriceHT($this->faker->randomFloat(2, 10, 1000))
                ->setImage($this->uploadImageArticle())
                ->setEnable($this->faker->boolean())
                ->setCreatedAt(
                    \DateTimeImmutable::createFromMutable($this->faker->dateTimeThisYear())
                );

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function uploadImageArticle(): UploadedFile
    {
        /** @var array<string> $files */
        $files = glob(realpath(__DIR__ . '/Images/Articles/') . '/*.*');

        $file = array_rand($files);

        $imageFile = new File($files[$file]);
        $uploadFile = new UploadedFile($imageFile, $imageFile->getFilename());

        return $uploadFile;
    }
}
