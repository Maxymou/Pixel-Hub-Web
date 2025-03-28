<?php $this->layout('layouts/main') ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Widgets de statistiques -->
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="widget" data-widget-id="stats-cpu">
                <div class="widget-header">
                    <h5 class="widget-title">Utilisation CPU</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Actualisé il y a <span class="last-update">quelques secondes</span></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="widget" data-widget-id="stats-memory">
                <div class="widget-header">
                    <h5 class="widget-title">Mémoire</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Actualisé il y a <span class="last-update">quelques secondes</span></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="widget" data-widget-id="stats-disk">
                <div class="widget-header">
                    <h5 class="widget-title">Espace disque</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Actualisé il y a <span class="last-update">quelques secondes</span></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="widget" data-widget-id="stats-network">
                <div class="widget-header">
                    <h5 class="widget-title">Réseau</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Actualisé il y a <span class="last-update">quelques secondes</span></small>
                </div>
            </div>
        </div>

        <!-- Widget des applications récentes -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="widget" data-widget-id="recent-apps">
                <div class="widget-header">
                    <h5 class="widget-title">Applications récentes</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ajouter une application">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Dernière utilisation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Les applications seront chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget des notifications -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="widget" data-widget-id="notifications">
                <div class="widget-header">
                    <h5 class="widget-title">Notifications</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Tout marquer comme lu">
                            <i class="fas fa-check-double"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="list-group list-group-flush">
                        <!-- Les notifications seront chargées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget des mises à jour -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="widget" data-widget-id="updates">
                <div class="widget-header">
                    <h5 class="widget-title">Mises à jour</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Vérifier les mises à jour">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="list-group list-group-flush">
                        <!-- Les mises à jour seront chargées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget des sauvegardes -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="widget" data-widget-id="backups">
                <div class="widget-header">
                    <h5 class="widget-title">Sauvegardes</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Créer une sauvegarde">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="list-group list-group-flush">
                        <!-- Les sauvegardes seront chargées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'ajout d'application -->
<div class="modal fade" id="addAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addAppForm">
                    <div class="mb-3">
                        <label for="appName" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="appName" required>
                    </div>
                    <div class="mb-3">
                        <label for="appType" class="form-label">Type</label>
                        <select class="form-select" id="appType" required>
                            <option value="">Sélectionner un type</option>
                            <option value="game">Jeu</option>
                            <option value="utility">Utilitaire</option>
                            <option value="media">Média</option>
                            <option value="network">Réseau</option>
                            <option value="system">Système</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="appPath" class="form-label">Chemin d'exécution</label>
                        <input type="text" class="form-control" id="appPath" required>
                    </div>
                    <div class="mb-3">
                        <label for="appIcon" class="form-label">Icône</label>
                        <input type="file" class="form-control" id="appIcon" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="addAppForm" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts spécifiques au tableau de bord -->
<script src="/js/dashboard.js"></script> 