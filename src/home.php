<?php
// Adding header
require_once('header.php');

// Exceptions
$ex_results = null;

// Filtrage par pseudo
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
if ($filter) {
    $stmt = $con->prepare("
            SELECT *, COUNT(commentaire.id_commentaire) AS comment_count 
            FROM article 
            LEFT JOIN commentaire ON article.id_article = commentaire.id_article
            LEFT JOIN utilisateur ON article.id_utilisateur = utilisateur.id_utilisateur
            LEFT JOIN reference ON article.id_article = reference.id_article
            LEFT JOIN categorie ON reference.id_categorie = categorie.id_categorie
            WHERE utilisateur.pseudo LIKE :filter 
            GROUP BY article.id_article 
            ORDER BY date DESC
        ");
    $stmt->bindValue(':filter', '%' . $filter . '%');
} else {
    $stmt = $con->prepare("
            SELECT *, COUNT(commentaire.id_commentaire) AS comment_count 
            FROM article 
            LEFT JOIN commentaire ON article.id_article = commentaire.id_article
            LEFT JOIN utilisateur ON article.id_utilisateur = utilisateur.id_utilisateur
            LEFT JOIN reference ON article.id_article = reference.id_article
            LEFT JOIN categorie ON reference.id_categorie = categorie.id_categorie
            GROUP BY article.id_article 
            ORDER BY date_article DESC
        ");
}

$stmt->execute();
$nb_row = $stmt->rowCount();
$article = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($article)) {
    $ex_results = "No results found!";
}


if (isset($_POST["delete-article"]) && isset($_POST["id-article"])) {
    $stmtComments = $con->prepare("DELETE FROM article WHERE id_article = ?");
    $stmtComments->bindParam(1,$_POST["id-article"]);
    $stmtComments->execute();

    header("Location: home.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<main class="max-w-2xl m-auto px-4">

    <div class="home-header flex justify-between my-9">

        <?php if ($_SESSION["isAdmin"] == true): ?>
            <form  method="post" action="../admin/categorie.php">

                <input type="submit" value="Access to CRUD" class="rounded-2xl px-3.5">

            </form>
        <?php endif; ?>

        <form method="get" action="home.php" class="flex w-7/12">

            <img src="./images/icons/gi_search.svg" alt="search-icon" class="bg-gray-50 rounded-l-2xl pl-3">
            <input type="text" name="filter" placeholder="Filter by username"
                   value="<?php echo htmlspecialchars($filter); ?>" class="w-full rounded-r-md outline-none px-3.5">
            <input type="submit" name="bt-filter" value="Filter" class="home-header-filter hidden sm:flex w-1/5 px-3.5">
            <input type="image" src="./images/icons/gi_filter.svg" alt="filter-con" class="home-header-filter flex sm:hidden px-2">

        </form>

        <!-- Button create an article -->
        <form action="add-article.php"> 

                <input type="submit" name="bt-create-article" value="Create an article" class="hidden sm:flex rounded-2xl px-3.5">
                <input type="image" src="./images/icons/gi_post.svg" alt="create-article-icon" class="flex sm:hidden rounded-2xl px-2">

        </form>

    </div>

    <!-- Article -->
    <div class="flex flex-col w-full m-auto shadow-xl">

        <?php foreach ($article as $row): ?>
            <?php
            if ($row == $article[0]) {
                $border_radius = "rounded-t-xl";
            } else if ($row == $article[$nb_row - 1]) {
                $border_radius = "rounded-b-xl";    
            } else {
                $border_radius = "rounded-none";
            }
            ?>

            <a href="article.php?id=<?php echo $row['id_article']; ?>&filter=<?php echo urlencode($filter); ?>">

                <div class="article w-full bg-gray-300 px-4 py-5 border-primary-400 border-b-2 <?php echo $border_radius; ?>">

                    <?php if (strtolower($row['id_utilisateur']) == $_SESSION["id"]): ?>
                    <form method="post" action="home.php" class="float-right">

                        <input type="hidden" name="id-article" value="<?php echo $row['id_article']; ?>">
                        <button type="submit" name="delete-article"><img src="./images/icons/gi_delete.svg" alt="delete-icon"></button>

                    </form>
                    <?php endif; ?>

                    <div class="text-xs mb-2 ">
                        <p class="font-medium"><?php echo $row['pseudo'] ?> / <?php echo $row['date_article'] ?> / <?php echo $row['nom'] ?></p>
                    </div>

                    <h1 class="article-title font-bold mb-1"><?php echo htmlspecialchars($row['titre']); ?></h1>
                    <p class="mb-4"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    <h2 class="text-md">&#128172;<?php echo htmlspecialchars($row['comment_count']); ?></h2>

                </div>
            </a>
        <?php endforeach; ?>

    </div>

    <p class="text-xl"><?php echo $ex_results; ?></p>

    <?php require_once('footer.php'); ?>
</main>

</body>
</html>
