# Mise en place d'un site e-commerce Symfony

Ce module a pour but de vous former au développement avec le framework Symfony en créant un site e-commerce. Nous verrons également l'intégration de l'API de paiement Stripe sur notre application pour la partie passerelle de paiement.

Prérequis pour ce module:

- Maitrise du web statique (HTML/CSS/JS)
- Maitrise des base du langage PHP
- Maitrise du langage SQL
- Maitrise de la POO (Programmation orientée objet)
- Maitrise des fondamentaux de Symfony

## Installation et configuration de l'application

Vous allez créer une nouvelle application symfony, vous pouvez le faire avec le cli symfony dans votre terminal de commande:

```bash
symfony new e-commerce --version="7.*" --webapp
```

Ensuite vous pouvez installer le bundle webpack encore en vous rendant dans le dossier de votre nouveau projet avec votre terminal et rentrer la commande:

```bash
composer require symfony/webpack-encore-bundle
```

N'oubliez pas ensuite d'installer les dépendances node avec la commande:

```bash
yarn install
```

Vous pouvez également configurer bootstrap sur votre projet, nous allons l'utiliser pour gagner du temps sur le développement du frontend de notre application, vous pouvez retrouver la documentation en [cliquant ici](https://symfony.com/doc/current/frontend/encore/bootstrap.html).

Vous pouvez également mettre en place Sass et faire votre structure scss ainsi que vos variables et mixins si besoin.

## Connexion en Base de données

Avant de commencer, vous devez faire la connexion à votre base de données, pour ce faire, créer votre fichier **.env.local** à la racine de votre projet et ajouter votre DATABASE_URL avec les bonnes informations.

Ensuite, pour la prise en compte, faite la commande:

```shell
composer dump-env dev
```

Ensuite créez votre base de données avec la commande:

```shell
symfony console doctrine:database:create
```

## Schéma de base de données

Avant de se lancer dans le développement, nous allons d'abord présenter le schéma MCD de la base de données, ce schéma est orienté Entity de Symfony pour vous aider dans la création des entity.

Lisez attentivement le schéma et analysez le bien avant de créer une entity.

> [!NOTE]
>
> Le schéma MCD est très important pour le développement de l'application, n'hésitez pas à revenir plusieurs fois dessus pendant votre développement pour vous aider

![Image schéma MCD](./docs/MCD_e-commerce.jpeg)

## Création de la table user

Nous aurons besoin d'une table utilisateur afin de pouvoir connecter les utilisateurs et effectuer le suivi des commandes, de plus nous aurons besoin de sécuriser notre application admin avec des rôles.
Pour créer un configurer votre entity user, rentrez la commande:

```shell
symfony console make:user
```

Ensuite vous pouvez rajouter à votre entity user les champs suivants:

- firstName
- lastName
- createdAt
- updatedAt

Effectuez maintenant vote migration en base de donnée pour créer votre table user.

### Faire vos fixtures

Afin de gagner du temps, vous pouvez mettre en place vos fixtures pour créer au minimum un utilisateur admin en base de données.

### Gérer la connexion et la déconnexion

Maintenant, vous devez mettre en place le système de connexion et déconnexion de votre plateforme, pour ce faire, vous pouvez suivre la documentation de symfony en [cliquant ici](https://symfony.com/doc/current/security.html#form-login).

### Gérer l'inscription de vos utilisateurs

Maintenant que la connexion et déconnexion et faite sur votre application, vous pouvez faire la page d'inscription, qui devra être publique.

#### Création du formulaire User

Vous devez dans un premier temps définir le formulaire d'inscription des utilisateurs, pensez que ce formulaire sera utilisé pour la création de compte, mais également pour la partie gestion des utilisateurs en admin, mais quand le formulaire sera généré sur la page d'admin, nous ne voulons pas ajouter le champs du mot de passe et ajouter celui des rôles.

Pour créer le formulaire vous pouvez faire la commande:

```shell
symfony console make:form UserType User
```

Cette commande va vous générer le fichier du formulaire, voici à quoi il doit ressembler:

```php
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom:',
                'required' => false,
                'attr' => [
                    'placeholder' =>  'John',
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom:',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Doe',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' =>  'Email:',
                'required' => false,
                'attr' => [
                    'placeholder' => 'john@example.com'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'invalid_message' =>  "Les mot de passe ne correspondent pas",
                'first_options' => [
                    'label' => "Mot de passe:",
                    'attr' => [
                        'placeholder' => "S3CR3T",
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'max' => 4096
                        ]),
                        new Assert\Regex(
                            pattern: '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d\s:])([^\s]){8,16}$/',
                            message: 'Le mot de passe doit contenir au minimum 1 lettre majuscule, minuscule, 1 chiffre et caractère spéciale'
                        )
                    ],
                    'help' => "Le mot de passe doit contenir au minimum 1 lettre majuscule, minuscule, 1 chiffre et caractère spéciale"
                ],
                'second_options' => [
                    'label' => "Confirmation mot de passe:",
                    'attr' => [
                        'placeholder' => "S3CR3T",
                    ],
                ]
            ]);

        if ($options['isAdmin']) {
            $builder->remove('password')
                ->add('enable', CheckboxType::class, [
                    'label' => 'Activé',
                    'required' => false,
                ])
                ->add('roles', ChoiceType::class, [
                    'label' => 'Roles:',
                    'placeholder' => 'Sélectionner un role',
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Administrateur' => 'ROLE_ADMIN',
                    ],
                    'expanded' => true,
                    'multiple' => true,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isAdmin' => false,
        ]);
    }
}
```

N'oubliez pas d'importer toutes les classes que nous utilisons dans le formulaire.

#### Gérez le controller et la vue

Maintenant que vous avez votre Formulaire, vous pouvez ajouter la méthode dans un controller qui va gérer la page d'inscription,  cette méthode doit ressemble à ça:

```php
#[Route('/register', 'app.register', methods: ['GET', 'POST'])]
public function register(
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher
): Response|RedirectResponse {
    $user = new User();

    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a bien été créé');

        return $this->redirectToRoute('app.login');
    }

    return $this->render('register.html.twig', [
        'form' => $form,
    ]);
}
```

Ensuite, vous n'avez plus qu'à faire votre vue pour afficher la page.

### Faire votre CRUD admin pour les utilisateurs

Maintenant que vous pouvez créer des utilisateurs, vous n'avez plus qu'à finir votre crud pour la partie administrateur des vos utilisateurs.

## Création de la table produit

Maintenant, nous allons devoir créer une table importante dans notre application e-commerce, la table produit.

Avant de la mettre en place, il faut d'abord analyser le schéma MCD, comment vous le voyez, il y a des relations à faire entre plusieurs table, tout d'abord nous allons mettre de côté la relation avec order que nous ferons plus tard, à l'étape de la mise en place de la partie e-commerce de l'application. 

### Gestion des Catégories

Dans un premier temps, vous allez devoir créer l'entity catégorie, suivez le schéma MCD pour mettre en place votre entity.

Ensuite, vous devez faire un crud sur la partie Admin de votre application pour gérer vos catégories.

Maintenant, vous devez savoir faire un crud simple, je vous laisse faire.

> [!TIP]
>
> Si vous  avez encore du mal à faire un crud, vous pouvez relire le support de cours sur les fondamentaux de Symfony

### Gestion des taxes

Une fois que vous pouvez gérer vos catégories, vous allez devoir gérer vos taxes.

Une fois encore, suivez le [schéma MCD](#Schéma de base de données) pour mettre en place votre entity.

Ensuite faite votre CRUD en admin pour gérer vos taxes.

### Création de la table produit

Maintenant que nous avons fait nos 2 entity, nous pouvons créer l'entity Produit en ajoutant les relations avec Taxe et Catégories.
