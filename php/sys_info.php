<strong><?php echo $_SESSION['MIQ']?> <?php echo $_SESSION['PROJECTNAME']?> (<?php echo explode(".", $_SESSION["DB_MAIN"])[0]?>)</strong>
<font style='font-size:10px;'>
<br>User: <?php echo $_SESSION["user_name"];?> (GRP: <?php echo $_SESSION['user_group'];?>)
<br>Login:<?php echo $_SESSION["startLog"];?>
<br>DB-Mode:<?php echo $_SESSION["DB_type"];?>
</font>
<br><br>
<!-- <label id="winboxStatusLabel">Lade Winbox-Status...</label> -->
<script>
// document.addEventListener('DOMContentLoaded', () => {
//     const statusLabel = document.getElementById('winboxStatusLabel');
//     if (!statusLabel) {
//         console.error("Fehler: Label mit der ID 'winboxStatusLabel' wurde nicht gefunden!");
//         return; // Funktion beenden, wenn Label fehlt
//     }
//     const updateWinboxStatus = () => {
//         try {
//             const winboxData = parent.get_open_winboxes();
//             const jsonString = JSON.stringify(winboxData, null, 2);
//             statusLabel.innerHTML = `<pre>${jsonString}</pre>`;
//             console.log('Winbox-Status aktualisiert:', winboxData);
//         } catch (error) {
//             console.error("Fehler beim Abrufen der Winbox-Daten:", error);
//             statusLabel.innerHTML = `Fehler beim Laden des Status. (Details in Konsole)`;
//         }
//     };
//     updateWinboxStatus();
//     setInterval(updateWinboxStatus, 60000);
// });

// open_winboxes = window.top.get_open_winboxes();
// console.log(open_winboxes);

</script> 
