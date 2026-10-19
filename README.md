# Comprendre le seeder

Le seeder est le programme qui copie les données de démonstration dans la base. Le fichier à lire est `database/seeders/DatabaseSeeder.php`; les données source se trouvent dans `database/data/Demon.json`, et les tables sont définies dans `database/migrations/2026_10_16_123901_create-tables.php`.

## Le parcours des données

Le seeder travaille dans cet ordre, car certaines lignes dépendent des précédentes :

1. Créer le compte Yaa-Awa.
2. Ajouter les catégories.
3. Ajouter les produits, liés aux catégories.
4. Ajouter les zones de livraison.
5. Ajouter six commandes et les articles de chacune.

Les promotions et les autres commandes du JSON ne sont pas importées par ce seeder.

## Explication du code

### 1. Lire le fichier JSON

```php
$data = json_decode(
 file_get_contents(database_path('data/Demon.json')),
 true,
 512,
 JSON_THROW_ON_ERROR,
);
```

- `database_path(...)` construit le chemin vers le fichier JSON dans le dossier `database`.
- `file_get_contents(...)` lit tout le fichier.
- `json_decode(..., true)` transforme le JSON en tableaux PHP. Par exemple, les catégories deviennent accessibles avec `$data['categories']`.
- `JSON_THROW_ON_ERROR` fait apparaître une erreur claire si le JSON est invalide, au lieu de continuer avec des données vides.

### 2. Créer le compte

`User::firstOrCreate(...)` cherche d’abord `yaa-awa@example.com`. Si le compte n’existe pas, il le crée avec le nom `Yaa-Awa` et le mot de passe indiqué. S’il existe déjà, il ne crée pas de doublon et ne change pas son mot de passe.

`Hash::make(...)` chiffre le mot de passe avant de l’enregistrer. Le seeder lit la variable `YAA_AWA_PASSWORD`; si elle n’est pas définie, le mot de passe provisoire est `password`.

### 3. Ajouter les catégories et produits

`array_map(...)` parcourt chaque entrée du JSON et fabrique une nouvelle liste avec les noms de colonnes attendus par la base. Par exemple, la clé JSON `categoryId` du produit devient `category_id` dans la table.

`DB::table(...)->upsert(...)` enregistre la liste. Pour une catégorie, l’`id` sert à reconnaître une ligne déjà présente : elle est ajoutée si elle n’existe pas, ou mise à jour si elle existe. Le même principe est utilisé pour les produits. Les IDs du JSON sont conservés pour que le produit `categoryId: "1"` reste lié à la catégorie `id: 1`.

`$timestamp = now()` fournit la date et l’heure courantes pour les colonnes `created_at` et `updated_at` des nouvelles catégories, produits et zones.

### 4. Ajouter les zones de livraison

Les zones sont importées avant les commandes, car chaque commande contient un `zoneId`. Le seeder convertit cet identifiant en nombre et copie le nom et le prix de livraison dans la table `deliveryZones`.

### 5. Ajouter les commandes et leurs articles

```php
foreach (array_slice($data['orders'], 0, 6) as $order) {
 // Une commande est préparée et enregistrée ici.
}
```

`array_slice(..., 0, 6)` prend les six premières commandes du JSON. `foreach` traite ces commandes une par une. Leurs statuts sont `EN_ATTENTE`, `CONFIRMEE`, `EN_PREPARATION`, `PRETE`, `TERMINEE` et `ANNULEE`.

Pour chaque commande :

- `Carbon::parse(...)` transforme les dates du JSON en dates compréhensibles par la base.
- Les champs de la commande sont copiés dans `orders`. Les noms comme `subtotal` et `status` sont convertis en `sub_total` et `statut`.
- `str()->uuid()` crée un identifiant unique pour `checkout_tocken`.
- Si le statut est `ANNULEE`, la date de mise à jour est également copiée dans `canceled_at`; sinon ce champ reste vide.
- La commande est reconnue par sa `reference`, par exemple `TM-DEMO-001`.
- Le seeder relit ensuite l'`id` créé dans `orders`. Cet ID est nécessaire pour rattacher les produits à cette commande dans `orders_items`.
- Chaque article reprend son produit, son nom, son prix, sa quantité et son total de ligne.

Certains noms de colonnes sont écrits de façon inhabituelle dans la migration, notamment `checkout_tocken`, `deleveryzone_id` et `products_id`. Le seeder reprend ces noms exactement pour correspondre au schéma actuel.

## Relancer le seeder

Pour créer les tables puis importer les données :

```bash
php artisan migrate --seed
```

Pour importer les données seulement, lorsque les tables existent déjà :

```bash
php artisan db:seed
```

Le compte est reconnu par son adresse, les catégories et produits par leurs IDs, les zones par leurs IDs et les commandes par leur référence. Relancer le seeder ne crée donc pas de doublons pour ces données. Il met à jour les lignes correspondantes, mais ne supprime pas de la base les lignes qui auraient été retirées du JSON.

## Mot de passe du compte

Définir `YAA_AWA_PASSWORD` dans le fichier `.env` permet de choisir le mot de passe du compte :

```env
YAA_AWA_PASSWORD=choisir-un-mot-de-passe
```

Sans cette variable, le mot de passe provisoire est `password`. Il faut le remplacer avant d’utiliser le compte hors d’un environnement local.

## Demande 02 — Carte produit et disponibilité

La Demande 02 est maintenant raccordée au catalogue. Lis les étapes ci-dessous dans l’ordre : elles expliquent comment les produits arrivent jusqu’à la carte et ce que chaque fichier fait.

### Responsabilités des fichiers

- `routes/web.php` dirige la page d’accueil vers `HomeController`.
- `app/Http/Controllers/HomeController.php` lit les produits actifs et mis en avant, puis les envoie à `Welcome.vue` avec Inertia. Il adapte aussi le chemin des images du JSON au dossier réel `public/assets/images`.
- `resources/js/pages/Welcome.vue` reçoit la liste et crée une carte par produit.
- `resources/js/components/base/ProductCard.vue` reçoit un produit, valide ses props, calcule son affichage et émet un événement `add`.
- `app/Http/Requests` sert à valider les données reçues par des actions HTTP Laravel. Il n’y a pas encore de formulaire ou de requête de création de produit : la validation demandée ici reste donc dans les props Vue.
- Les styles de la carte existent déjà dans `public/assets/css/styles.css` et `public/assets/css/template.css`; le composant réutilise leurs classes.

Le parcours est : route → contrôleur → props Inertia → page `Welcome` → prop `product` de chaque `ProductCard`.

## Étape 1 — Comprendre la prop `product`

Dans `<script setup>`, `defineProps` déclare ce que le composant reçoit. Ici, la carte attend un objet `product`. `required: true` veut dire qu’il est obligatoire; `type: Object` indique le type attendu; `validator` vérifie ensuite les champs importants.

Le validateur vérifie l’ID, le nom, l’image, l’unité, l’origine, le prix, le stock et le prix promotionnel. Compare ces champs avec un produit dans `database/data/Demon.json`. Le validateur Vue aide pendant le développement, mais n’est pas une règle de sécurité côté serveur.

Il n’y a pas de formulaire ou de route d’écriture de produit actuellement. C’est pourquoi il n’y a pas de `ProductRequest` à créer pour cette demande. Si un jour l’application reçoit un nouveau produit par une requête HTTP, ses règles devront être ajoutées dans un Form Request Laravel.

## Étape 2 — Comprendre les valeurs calculées

`computed` permet de calculer une valeur à partir du produit reçu. La carte utilise trois calculs :

- `stockStatus` transforme le stock en `empty` (zéro), `low` (de 1 à 5) ou `available` (plus de 5).
- `hasPromotion` est vrai seulement si `salePrice` est un entier positif inférieur au prix normal.
- `displayedPrice` choisit le prix promotionnel si la promotion est valable, sinon le prix normal.

Ainsi, le template ne répète pas les comparaisons de prix et de stock. Quand la prop change, Vue recalcule ces valeurs automatiquement.

## Étape 3 — Lire le template de la carte

Dans le `<template>` de `ProductCard.vue`, `product.name` affiche le nom. Les attributs commençant par `:` sont liés aux données : `:src` met l’image dans l’élément `<img>`, `:alt` donne son texte alternatif, et `:class` choisit la classe CSS correspondant à `stockStatus`.

`v-if`, `v-else-if` et `v-else` affichent un seul libellé de stock. `v-if="hasPromotion"` masque complètement la pastille et l’ancien prix en l’absence de promotion. Le bouton utilise `:disabled="product.stock === 0"`, donc le navigateur empêche le clic quand le produit est en rupture.

Le prix est formaté en français avec `Intl.NumberFormat('fr-FR')`, puis le texte `FCFA` est ajouté. Les classes `product-bottom` et `add-button` sont reprises de la maquette pour conserver sa mise en page.

## Étape 4 — Comprendre la communication du bouton

`defineEmits(['add'])` déclare l’événement `add`. La fonction `addToCart` ne l’émet que si le stock est supérieur à zéro. Un composant enfant ne modifie pas directement le panier global : il signale l’action au parent.

Il n’existe pas encore de store de panier dans le projet. `Welcome.vue` ne traite donc pas encore cet événement; le bouton est désactivé correctement en rupture, mais un clic en stock positif n’ajoute pas encore réellement l’article. La gestion du panier est une fonctionnalité distincte.

## Étape 5 — Réutiliser la maquette

L’animation `.product-card:hover` existe dans `public/assets/css/template.css`. Les styles de `.stock`, `.stock.low`, `.stock.empty`, `.promo-tag`, `.product-bottom` et `.add-button` existent dans `public/assets/css/styles.css`. Le composant réutilise ces classes; son bloc `<style>` reste vide pour éviter une deuxième version de ces styles.

## Étape 6 — Vérifier les règles

- Stock `0` : texte `Rupture`, style rouge, bouton désactivé.
- Stock `1` et `5` : texte `Stock faible`.
- Stock `6` : texte `Disponible`.
- `salePrice: null` : pas de pastille, seul le prix normal apparaît.
- Prix promo positif et inférieur au prix normal : pastille `Promo`, ancien prix barré, nouveau prix affiché.
- Prix promo `0`, négatif ou supérieur/égal au prix normal : pas de promotion affichée.
- Un produit avec du stock émet `add` quand on clique; un produit à stock nul ne peut pas être cliqué.

Après l’intégration, lancer les contrôles frontend du projet :

```bash
npm run check
npm run types:check
npm run build
```

## Chemin des données jusqu'à la vue

Voici le trajet d'un produit, depuis sa donnée source jusqu'à son image affichée :

```text
Demon.json
 -> DatabaseSeeder
 -> table products dans MySQL
 -> GET /
 -> HomeController et modèle Product
 -> props Inertia de Welcome.vue
 -> ProductCard.vue
 -> élément <img> dans le navigateur
```

### 1. Les données source

Dans `database/data/Demon.json`, chaque produit a des informations comme son nom, son prix, son stock et son image. Par exemple, le riz a `"image": "/images/riz.svg"`.

Le JSON ne contient pas le dessin de l'image. Le fichier existe séparément dans `public/assets/images/riz.svg`.

### 2. Le seeder enregistre les données

`database/seeders/DatabaseSeeder.php` lit le JSON et copie ses catégories et produits dans les tables. Pour le riz, la colonne `products.image` contient donc le texte `/images/riz.svg`. Elle ne contient ni l'image ni une URL complète.

Après une modification du JSON, `php artisan db:seed` relance cet import si les tables existent déjà. `php artisan migrate:fresh --seed` recrée d'abord toutes les tables et efface les données précédentes : n'utiliser cette commande que si la base peut être vidée.

### 3. La route appelle le contrôleur

Dans `routes/web.php`, la route `GET /` appelle `HomeController::index()`. C'est cette route qui construit la page d'accueil.

Le fichier `app/Models/Product.php` représente la table `products`. Dans `app/Http/Controllers/HomeController.php`, une requête au modèle sélectionne les produits actifs et mis en avant. Le contrôleur adapte ensuite le chemin de l'image :

```text
Valeur en base : /images/riz.svg
Préfixe ajouté : /assets
URL envoyée à la page : /assets/images/riz.svg
Fichier correspondant : public/assets/images/riz.svg
```

Le fichier placé dans `public` est servi directement par Laravel. Le navigateur demande donc `/assets/images/riz.svg`, et Laravel répond avec le fichier `public/assets/images/riz.svg`.

### 4. Inertia transmet les produits

Le contrôleur retourne `Inertia::render('Welcome', ['products' => $products])`. Inertia envoie la liste `products` à la page Vue `resources/js/pages/Welcome.vue`.

Dans `Welcome.vue`, `defineProps` déclare cette liste. Le `v-for` parcourt les produits et passe l'objet courant à `ProductCard` avec `:product="product"`. `:key="product.id"` aide Vue à reconnaître chaque carte.

### 5. La carte demande l'image au navigateur

Dans `resources/js/components/base/ProductCard.vue`, `product` est la prop reçue. La liaison `:src="product.image"` demande au navigateur d'afficher l'URL préparée par le contrôleur; `:alt="product.name"` fournit la description textuelle de l'image.

La route `GET /api/products`, aussi déclarée dans `routes/web.php`, est une autre manière de renvoyer des produits en JSON. La page d'accueil actuelle ne l'utilise pas : elle passe par Inertia et `HomeController`.

### Où chercher si l'image manque

1. Vérifier que le fichier existe dans `public/assets/images/`.
2. Vérifier la valeur de `image` dans `database/data/Demon.json` et dans la colonne `products.image`.
3. Vérifier que `HomeController` transmet bien une URL `/assets/images/...`.
4. Dans le navigateur, ouvrir directement cette URL. Pour le riz en local : `http://localhost:8000/assets/images/riz.svg`.
5. Dans les outils de développement du navigateur, inspecter `src` sur l'élément `<img>` et vérifier qu'il ne renvoie pas une erreur 404.
