<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Gestion des Mises à Jour</h1>
        <button id="checkUpdatesBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Vérifier les Mises à Jour
        </button>
    </div>

    <!-- Section des mises à jour disponibles -->
    <div id="availableUpdates" class="mb-8 hidden">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Mises à Jour Disponibles</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Version
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="availableUpdatesList" class="bg-white divide-y divide-gray-200">
                    <!-- Les mises à jour disponibles seront injectées ici -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section des mises à jour téléchargées -->
    <div id="downloadedUpdates" class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Mises à Jour Téléchargées</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Version
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="downloadedUpdatesList" class="bg-white divide-y divide-gray-200">
                    <!-- Les mises à jour téléchargées seront injectées ici -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de téléchargement -->
<div id="downloadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Téléchargement de la Mise à Jour</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Progression</span>
                    <span id="downloadProgress" class="text-sm text-gray-700">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="downloadBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <div id="downloadStatus" class="text-sm text-gray-500"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de planification -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Planifier l'Installation</h3>
            <form id="scheduleForm" class="space-y-4">
                <input type="hidden" name="version" id="scheduleVersion">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date et Heure</label>
                    <input type="datetime-local" name="scheduled_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" class="cancel-modal px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Planifier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmation</h3>
            <p id="confirmMessage" class="text-sm text-gray-500"></p>
            <div class="flex justify-end space-x-3 mt-4">
                <button class="cancel-modal px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Annuler
                </button>
                <button id="confirmAction" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                    Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 