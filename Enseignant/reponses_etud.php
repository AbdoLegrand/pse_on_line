<?php
session_start();
$email = $_SESSION['email'];
if ($_SESSION["role"] != "ens") {
    header("location:../authentification.php");
}
include_once "../connexion.php";
$id_sous = $_GET['id_sous'];
$sql_matiere = "SELECT  id_matiere, matiere.libelle as 'libelle_matiere', type_soumission.libelle as 'libelle_type'  FROM soumission INNER JOIN matiere USING(id_matiere) INNER JOIN type_soumission USING(id_type_sous) WHERE id_sous = $id_sous";
$req_matiere = mysqli_query($conn, $sql_matiere);
$row_matiere = mysqli_fetch_assoc($req_matiere);
$id_matiere = $row_matiere['id_matiere'];
$libelle_matiere = $row_matiere['libelle_matiere'];
$libelle_type = $row_matiere['libelle_type'];


if (isset($_POST['enoyer_note'])) {
    $sql = "UPDATE reponses SET render=1 WHERE id_sous='$id_sous'";
    mysqli_query($conn, $sql);

    $sql_tou = "SELECT * FROM `inscription` WHERE inscription.id_matiere='$id_matiere'";
    $req_tou = mysqli_query($conn, $sql_tou);
    while ($row_tou = mysqli_fetch_assoc($req_tou)) {
        $id_etud = $row_tou['id_etud'];
        $sql_tout = "SELECT * FROM `etudiant` where id_etud=$id_etud";
        $req_tout = mysqli_query($conn, $sql_tout);
        $row_tout = mysqli_fetch_assoc($req_tout);
        $subject = "Notes de :   $libelle_matiere";

        $message = "Les notes de  `$libelle_type` de la matière  `$libelle_matiere` sont disponible sur la plateforme.";

        $url =  "https://script.google.com/macros/s/AKfycbz1KWjBC8wx3Ay9fYYg6pW_1dcS-07rYT07Xxq0SscKOgUXpiPcq5zqgfTsR7PZFr4j/exec";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POSTFIELDS => http_build_query([
                "recipient" => $row_tout['matricule'],
                "subject"   => $subject,
                "body"      => $message
            ])
        ]);

        $result = curl_exec($ch);
    }

    if ($result) {
        $_SESSION['ajout_reussi'] = true;
    }
}
$sql_affichage = "SELECT * FROM reponses, etudiant WHERE reponses.id_sous='$id_sous' AND reponses.id_etud=etudiant.id_etud;";

$req_affichage = mysqli_query($conn, $sql_affichage);
include "nav_bar.php";
?>


<?php
$req_detail = "SELECT * FROM soumission INNER JOIN matiere USING(id_matiere), enseignant WHERE id_sous = $id_sous AND soumission.id_ens=enseignant.id_ens ";
$req = mysqli_query($conn, $req_detail);
$row_sous = mysqli_fetch_assoc($req);
$sql1 = "SELECT COUNT(*) as num_rep FROM reponses WHERE id_sous = $id_sous ";
$req1 = mysqli_query($conn, $sql1);
$row1 = mysqli_fetch_assoc($req1);

$sql2 = "SELECT COUNT(*) as num_insc FROM inscription, matiere, soumission WHERE inscription.id_matiere=matiere.id_matiere and matiere.id_matiere=soumission.id_matiere and id_sous = $id_sous; ";
$req2 = mysqli_query($conn, $sql2);
$row2 = mysqli_fetch_assoc($req2);
?>

<div class="content-wrapper">
    <div class="content">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span> <a href="choix_semester.php">Accuei</a>
                <?php echo " / " ?>
                <a href="index_enseignant.php?id_semestre=<?php echo $_SESSION['id_semestre']; ?>"><?php echo "S" . $_SESSION['id_semestre']; ?></a>
                <?php echo " / " ?><a href="soumission_par_matiere.php"><?php echo $row_sous['libelle'] ?></a>
                <?php echo " / " ?><a href="#"><?php echo $row_sous['titre_sous']; ?></a>
                <?php $_SESSION['titre_sous'] = $row_sous['titre_sous']; ?>
            </h3>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-md-9 grid-margin">

                    <div class="card">
                        <div class="card-body">
                            <h4 class="text-center">Description de la soumission</h4><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class=" "> <?php echo "<strong>Titre :&nbsp; </strong>" . $row_sous['titre_sous']; ?></p>
                                    <p class=" "><?php echo "<strong>Description :&nbsp; </strong>" . $row_sous['description_sous']; ?></p>
                                    <p class=" "> <?php echo "<strong>Code de la matière :&nbsp; </strong>" . $row_sous['code']; ?></p>
                                </div>

                                <div class="col-md-6">
                                    <p class=""> <?php echo "<strong>Date de début : &nbsp;</strong>" . $row_sous['date_debut']; ?></p>
                                    <p class=""><?php echo "<strong>Date de fin :&nbsp; </strong>" . $row_sous['date_fin']; ?></p>
                                    <p class=""> <?php echo "<strong>Enseignant :&nbsp; </strong>" . $row_sous['nom'] . " " . $row_sous['prenom']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-description">Nombre d'étudiants ayant répondu </h4>
                            <div class="media">
                                <div class="media-body">
                                    <center>
                                        <p class="card-text display-3"><?php echo $row1['num_rep'] . "/" . $row2['num_insc']; ?></p>
                                    </center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="display: flex ; justify-content: space-between;">
                    <div>
                        <a href="list_etudiant.php?id_matiere=<?= $row_sous['id_matiere'] ?>&id_sous=<?= $id_sous ?>" class="btn btn-gradient-primary">Liste des étudiants inscrits</a>
                    </div>
                    <?php if (mysqli_num_rows($req_affichage) > 0) { ?>
                        <div>
                            <a href="exporter_note.php?id_sous=<?= $id_sous ?>&id_matiere=<?= $row_sous['id_matiere'] ?>" class="btn btn-primary">Exporter les notes</a>
                        </div>
                        <div>
                            <form action="" method="POST">
                                <input type="submit" class="btn btn-gradient-primary ml-25" value="Envoyer les Notes" name="enoyer_note">
                            </form>
                        </div>
                    <?php
                    }

                    $req_detail = "SELECT soumission.id_sous ,etudiant.id_etud,etudiant.id_groupe ,matricule,nom,prenom FROM soumission,etudiant,inscription WHERE   soumission.id_matiere = inscription.id_matiere and etudiant.id_etud = inscription.id_etud and soumission.id_sous = $id_sous;";
                    $req = mysqli_query($conn, $req_detail);
                    ?>
                </div>


                <div class="row">
                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Les réponses des étudiants :</h4>
                                <table id="example" class="table table-bordered" style="width:100%">
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Nom et prénom</th>
                                        <th>Groupe</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Détails</th>
                                    </tr>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($req)) {
                                        $id_sous = $row['id_sous'];
                                        $id_etud = $row['id_etud'];
                                        $req_detail2 = "SELECT * FROM reponses WHERE  id_sous = $id_sous and id_etud = $id_etud";
                                        $req2 = mysqli_query($conn, $req_detail2);
                                        if (mysqli_num_rows($req2) > 0) {
                                            $row2 = mysqli_fetch_assoc($req2);
                                            $status = ($row2['confirmer'] == 1) ? "<label class='badge badge-success'>Confirmé<label>" : "<label class='badge badge-warning'>Non-confirmé<label>";
                                    ?>
                                            <tr <?php if ($row2['confirmer'] == 1) { ?> class="table-success" <?php } else { ?> class="table-warning" <?php } ?>>
                                                <td><?php echo $row['matricule'] ?></td>
                                                <td><?php echo $row['nom'];
                                                    echo $row['prenom'] ?></td>
                                                <td style="text-align:center"><?php echo "G" . $row['id_groupe'] ?></td>
                                                <td><?php echo $row2['date'] ?></td>
                                                <td><?php echo $status ?></td>
                                                <td><a style="text-decoration:None" href="consiltation_de_reponse.php?id_rep=<?php echo $row2['id_rep']; ?>&titre_sous=<?= $row_sous['titre_sous'] ?>">Consulter</a></td>
                                            </tr>
                                        <?php
                                        }
                                    }
                                    $req_detail = "SELECT soumission.id_sous ,etudiant.id_etud,etudiant.id_groupe ,matricule,nom,prenom FROM soumission,etudiant,inscription WHERE   soumission.id_matiere = inscription.id_matiere and etudiant.id_etud = inscription.id_etud and soumission.id_sous = $id_sous;";
                                    $req = mysqli_query($conn, $req_detail);
                                    while ($row = mysqli_fetch_assoc($req)) {
                                        $id_sous = $row['id_sous'];
                                        $id_etud = $row['id_etud'];
                                        $req_detail2 = "SELECT * FROM reponses WHERE  id_sous = $id_sous and id_etud = $id_etud";
                                        $req2 = mysqli_query($conn, $req_detail2);
                                        if (mysqli_num_rows($req2) == 0) {
                                        ?>
                                            <tr class="table-danger">
                                                <td><?php echo $row['matricule'] ?></td>
                                                <td><?php echo $row['nom'] . " " . $row['prenom'] ?></td>
                                                <td style="text-align:center"><?php echo "G" . $row['id_groupe'] ?></td>

                                                <td></td>
                                                <td><label class="badge badge-danger">En attente</label></td>
                                                <td></td>
                                            </tr>
                                    <?php
                                        }
                                    }

                                    ?>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <?php
        // if (isset($_SESSION['exporte_ressi']) && $_SESSION['exporte_ressi'] === true) {
        //     echo " <script>
        //             const Toast = Swal.mixin({
        //                 toast: true,
        //                 position: 'top-start',
        //                 showConfirmButton: false,
        //                 timer: 3000,
        //                 timerProgressBar: true,
        //                 didOpen: (toast) => {
        //                     toast.onmouseenter = Swal.stopTimer;
        //                     toast.onmouseleave = Swal.resumeTimer;
        //                 }
        //             });
        //             Toast.fire({
        //                 icon: 'info',
        //                 title: 'Exportation réussi '
        //             });
        //             </script>";

        //     // Supprimer l'indicateur de succès de la session
        //     unset($_SESSION['exporte_ressi']);
        // }
        if (isset($_SESSION['ajout_reussi']) && $_SESSION['ajout_reussi'] === true) {
            echo '<script>
            Swal.fire({
                title: "Envoi réussi",
                text: "Les notes ont été envoyées avec succès 🎉🎉",
                icon: "success"
                });
            </script>';

            // Supprimer l'indicateur de succès de la session
            unset($_SESSION['ajout_reussi']);
        }
        ?>