// topicUrl doit être le topic relatif que tu passes depuis Twig
const topicUrl = "{{ topicUrl }}"; // en Twig, ça injecte /conversations/ID

// Connexion au hub Mercure
const eventSource = new EventSource(
    '/.well-known/mercure?topic=' + encodeURIComponent(topicUrl)
);

// Quand un message arrive
eventSource.onmessage = function(event) {
    const data = JSON.parse(event.data);
    console.log("Nouveau message :", data);

    // Tu peux ici mettre à jour ton DOM pour afficher le message
};
