<?php

/**
 * Classe Contrôleur des requêtes de l'interface Cellier.
 */

class ControleurCellier extends Routeur {

  private $action;
  private $bouteille_id;
  private $cellier_id;

  private $methodes = [
    'a' => 'ajouterBouteilleCellier',
    'b' => 'boireBouteilleCellier',
    'c' => 'autocompleteBouteille',
    'd' => 'afficherFicheBouteille',
    'l' => 'listeBouteille',
    'm' => 'modifierBouteilleCellier',
    'n' => 'ajouterNouvelleBouteilleCellier',
    'o' => 'listeCellier',
    'p' => 'ajouterCellier',
    'q' => 'modifierCellier',
    'r' => 'obtenirDetailsBouteille',
    's' => 'supprimerCellier'
  ];

  /**
   * Constructeur qui initialise la propriété oRequetesSQL déclarée dans la classe Routeur.
   * 
   */
  public function __construct() {
    $this->action = $_GET['action'] ?? 'o';
    $this->bouteille_id = $_GET['bouteille_id'] ?? null;
    $this->cellier_id = $_GET['cellier_id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  
  public function listeCellierTemporaire(){
    
    // TODO Enlever cette méthode lors de l'intégration de Twig et HTML/CSS
    new Vue("/Cellier/vCelliers",
        array(
          'titre'       => "Cellier",
 
        ),
      "/Frontend/gabarit-frontend");
  }



  public function listeBouteilleTemporaire(){
    
    // TODO Enlever cette méthode lors de l'intégration de Twig et HTML/CSS
    new Vue("/Cellier/vBouteilles",
        array(
          'titre'       => "Bouteille",

        ),
      "/Frontend/gabarit-frontend");
  }


  /**
   * Redirige les requêtes de l'interface Cellier vers les méthodes demandées.
   * 
   * @throws Exception Si l'action spécifiée dans la requête n'existe pas
   * @return void
   */  
  public function gererCellier() {

    if (isset($this->methodes[$this->action])) {
      $methode = $this->methodes[$this->action];
      $this->$methode();
    } else {
      throw new Exception("L'action $this->action n'existe pas.");
    }

  }

  /**
   * Ajouter une nouvelle bouteille au cellier.
   * 
   * @throws Exception Si la bouteille insérée contient des informations invalides
   * @return void
   */
  public function ajouterNouvelleBouteilleCellier() {

    //TODO remplacer par vrai id de l'utilisateur
    $utilisateur_id = 1;

    $body = json_decode(file_get_contents('php://input'));

    if(!empty($body)){

      // Création d'un objet Bouteille pour contrôler la saisie
      $oBouteille = new Bouteille([
          'id_bouteille'  => $body->id_bouteille,
          'id_cellier'    => $body->id_cellier,
          'quantite'      => $body->quantite
      ]);
      
      $erreursBouteille = $oBouteille->erreurs;

      if (count($erreursBouteille) === 0) {

        $resultat = $this->oRequetesSQL->ajouterBouteilleCellier([
          'id_bouteille'  => $oBouteille->id_bouteille,
          'id_cellier'    => $oBouteille->id_cellier,
          'quantite'      => $oBouteille->quantite,
        ]);

        echo json_encode($resultat);
      }
      else {
        // Pas supposé étant donné la validation front-end
        throw new Exception("Erreur: bouteille invalide, non insérée:" . implode($oBouteille->erreurs));
      }

    }
    else{

      $cellier_preferentiel = $this->cellier_id ?? null;
      $celliers = $this->oRequetesSQL->obtenirListeCelliers($utilisateur_id);
      $pays = $this->oRequetesSQL->obtenirListePays();
      $types = $this->oRequetesSQL->obtenirListeTypes();

      new Vue("/Cellier/vAjoutBouteille",
        array(
          'titre'                 => "Ajout de bouteille",
          'cellier_preferentiel'  => $cellier_preferentiel,
          'celliers'              => $celliers,
          'pays'                  => $pays,
          'types'                 => $types
        ),
      "/Frontend/gabarit-frontend");
    }

  }

  /**
   * Recherche le cellier par nom de bouteille. Renvoit la liste des bouteilles trouvées
   * en format JSON.
   * 
   * @return void
   */
  public function autocompleteBouteille() {

    #TODO utilisateur id hardcodé à 1
    $utilisateur_id = 1;

    $body = json_decode(file_get_contents('php://input'));
          
    $listeBouteilles = $this->oRequetesSQL->autocomplete($body->nom, $utilisateur_id);
          
    echo json_encode($listeBouteilles);
  }

  /**
   * Modifier une bouteille du cellier.
   * 
   * @throws Exception Si la requête de modification de bouteille contient des informations invalides,
   *                   ou le bouteille_id d'une requête JSON est invalide.
   * @return void
   */
  public function modifierBouteilleCellier() {

    $body = json_decode(file_get_contents('php://input'));

    if(!empty($body)){

      // Création d'un objet Bouteille pour contrôler la saisie
      $oBouteille = new Bouteille([
          'id_bouteille_cellier'=> $body->id_bouteille_cellier,
          'id_bouteille'       => $body->id_bouteille,
          'date_achat'         => $body->date_achat,
          'garde_jusqua'       => $body->garde_jusqua,
          'notes'              => $body->notes,
          'prix'               => $body->prix,
          'quantite'           => $body->quantite,
          'millesime'          => $body->millesime
      ]);
      
      $erreursBouteille = $oBouteille->erreurs;

      if (count($erreursBouteille) === 0) {

        $resultat = $this->oRequetesSQL->modifierBouteilleCellier([
          'id_bouteille_cellier'=> $oBouteille->id_bouteille_cellier,
          'id_bouteille'        => $oBouteille->id_bouteille,
          'date_achat'          => $oBouteille->date_achat,
          'garde_jusqua'        => $oBouteille->garde_jusqua,
          'notes'               => $oBouteille->notes,
          'prix'                => $oBouteille->prix,
          'quantite'            => $oBouteille->quantite,
          'millesime'           => $oBouteille->millesime
        ]);

        echo json_encode($resultat);
      }
      else {
        // Pas supposé étant donné la validation front-end
        throw new Exception("Erreur: bouteille invalide, non insérée:" . implode($oBouteille->erreurs));
      }

    }
    else{
      if (!$this->bouteille_id) {
        throw new Exception(self::ERROR_BAD_REQUEST);
      }

      $bouteilleAModifier = $this->oRequetesSQL->obtenirBouteilleCellier($this->bouteille_id);

      new Vue("/Cellier/vModificationBouteille",
        array(
          'titre'       => "Modification de bouteille",
          'bouteille'   => $bouteilleAModifier
        ),
      "/Frontend/gabarit-frontend");
    }

  }

  /**
   * Incrémente la quantité pour une bouteille donnée.
   * 
   * @throws Exception Si l'id de la bouteille à ajouter est invalide, ou si la modification
   *                   à la base de données ne réussit pas.
   * @return void
   */
  private function ajouterBouteilleCellier()
  {
    $body = json_decode(file_get_contents('php://input'));
    
    // Création d'un objet Bouteille pour contrôler la saisie
    $oBouteille = new Bouteille([
      'id_bouteille_cellier'=> $body->id
    ]);

    if (count($oBouteille->erreurs) === 0) {
      $resultat = $this->oRequetesSQL->modifierQuantiteBouteilleCellier($oBouteille->id_bouteille_cellier, 1);
    }
    else {
      throw new Exception("Id invalide pour incrément de la quantité de bouteilles.");
    }

    if (!$resultat) {
      throw new Exception("Incrément de la quantité de bouteilles non mis à jour dans la db.");
    }

    echo json_encode($resultat);
  }

  /**
   * Décrémente la quantité pour une bouteille donnée.
   * 
   * @throws Exception Si l'id de la bouteille à décrémenter est invalide
   * @return void
   */
  private function boireBouteilleCellier()
  {
    $body = json_decode(file_get_contents('php://input'));
    
    // Création d'un objet Bouteille pour contrôler la saisie
    $oBouteille = new Bouteille([
      'id_bouteille_cellier'=> $body->id
    ]);

    if (count($oBouteille->erreurs) === 0) {
      $resultat = $this->oRequetesSQL->modifierQuantiteBouteilleCellier($oBouteille->id_bouteille_cellier, -1);
    }
    else {
      throw new Exception("Id invalide pour décrément de la quantité de bouteilles.");
    }

    echo json_encode($resultat);
  }

  /**
   * Liste les celliers pour un utilisateur donné.
   * 
   * @return void
   */
  public function listeCellier() {

    //TODO Codé en dur pour le moment, à remplacer
    $utilisateur_id = 1;

    // Extraction nom et id de tous les celliers de l'utilisateur
    $celliers = $this->oRequetesSQL->obtenirListeCelliers($utilisateur_id);

    $celliers_details = [];
    foreach ($celliers as $cellier) {

      // Extraction et calcul des proportions pour chaque type de vin
      $quantites_cellier = $this->oRequetesSQL->obtenirQuantitesCellier($cellier['id']);
      $total_bouteilles = Utilitaires::calculerTotalBouteilles($quantites_cellier);
      $cellier_details = [];

      if ($total_bouteilles > 0) {
        $proportions_cellier = Utilitaires::calculerProportionsTypes($quantites_cellier);
        $cellier_details['pourcentages'] = Utilitaires::formerDiagrammeCirculaire($proportions_cellier);
      }

      // Remettre toutes les infos dans une variable pour Twig
      $cellier_details['id'] = $cellier['id'];
      $cellier_details['nom'] = $cellier['nom'];
      $cellier_details['quantite'] = $total_bouteilles;
      $celliers_details[] = $cellier_details;
    }

    new Vue("/Cellier/vListeCelliers",
      array(
        'titre'     => "Vos celliers",
        'celliers'  => $celliers_details
      ),
      "/Frontend/gabarit-frontend");
  }

  /**
   * Liste les bouteilles pour un cellier donné.
   * 
   * @return void
   */  
  public function listeBouteille() {

    $bouteilles = $this->oRequetesSQL->obtenirListeBouteilleCellier($this->cellier_id);

    $cellier = $this->oRequetesSQL->obtenirNomCellier($this->cellier_id);

    new Vue("/Cellier/vListeBouteilles",
      array(
        'titre'       => "Détails du cellier",
        'bouteilles'  => $bouteilles,
        'cellier'     => $cellier
      ),
      "/Frontend/gabarit-frontend");
  }

  /**
   * Ajoute un cellier pour l'utilisateur authentifié.
   * 
   * @throws Exception Si une erreur survient lors de l'insertion du cellier
   * @return void
   */
  public function ajouterCellier() {

    //TODO Codé en dur pour le moment, à remplacer
    $utilisateur_id = 1;

    $oCellier = [];
    $erreursCellier = [];

    if (count($_POST) !== 0) {

      // Retour de saisie du formulaire
      $oCellier = new Cellier([
        'nom'       => $_POST['nom'],
        'id_membre' => $utilisateur_id
      ]); 


      $erreursCellier = $oCellier->erreurs;

      if (count($erreursCellier) === 0) {
        $resultat = $this->oRequetesSQL->ajouterCellier([
          'nom'       =>  $oCellier->nom,
          'idmembre'  =>  $oCellier->id_membre
        ]);

        if (!$resultat) {
          throw new Exception("Une erreur est survenue lors de l'insertion du cellier");
        }

        $this->listeCellier();
        exit;
      }
    }

    new Vue("/Cellier/vAjoutCellier",
      array(
        'titre'     => "Ajouter un cellier",
        'cellier'   => $oCellier,
        'erreurs'    => $erreursCellier
      ),
      "/Frontend/gabarit-frontend");

  }

  /**
   * Affiche la fiche détaillée pour une bouteille donnée.
   * 
   * @throws Exception Si la requête de lecture des détails échoue.
   * @return void
   */
  public function afficherFicheBouteille() {

    $bouteille = $this->oRequetesSQL->obtenirDetailsBouteilleCellier($this->bouteille_id);

    if (!$bouteille) {
      throw new Exception(self::ERROR_BAD_REQUEST);
    }

    new Vue("/Cellier/vFicheBouteille",
      array(
        'titre'     => "Fiche détaillée",
        'bouteille'   => $bouteille
      ),
      "/Frontend/gabarit-frontend");
  }

  /**
   * Modifie le nom d'un cellier.
   * 
   * @throws Exception Si une requête est faite sans envoi du numéro de cellier
   * @return void
   */
  public function modifierCellier() {

    //TODO: vérifier que le cellier appartient bien à l'usager

    $body = json_decode(file_get_contents('php://input'));

    if(!empty($body)){

      $resultat = $this->oRequetesSQL->modifierCellier([
        'cellier_id'  => $body->cellier_id,
        'nom'         => $body->nom
      ]);

      echo json_encode($resultat);

    }
    else {
      if (!$this->cellier_id) {
        throw new Exception(self::ERROR_BAD_REQUEST);
      }

      $cellier = $this->oRequetesSQL->obtenirNomCellier($this->cellier_id);

      new Vue("/Cellier/vModificationCellier",
        array(
          'titre'       => "Modification du cellier",
          'cellier'     => $cellier
      ),
      "/Frontend/gabarit-frontend");
    }
  }

  /**
   * Supprime un cellier avec tout son contenu.
   * 
   * @return void
   */
  public function supprimerCellier() {
    //TODO: Vérifier que le cellier appartient bien à l'usager avant de supprimer

    //TODO : suppression
  }

  /**
   * Donne la liste des détails pour une bouteille donnée du catalogue.
   * 
   * @return void
   */
  public function obtenirDetailsBouteille() {

    //TODO Vérifier que l'usager a la permission pour lire cette bouteille

    $body = json_decode(file_get_contents('php://input'));

    $bouteille = $this->oRequetesSQL->obtenirBouteilleCellier($body->id_bouteille);
            
    echo json_encode($bouteille);

  }
}