<br />
<div align="center">
  <img src="https://raw.githubusercontent.com/Jasson44/AfkSystem/refs/heads/master/assets/icon.png" alt="Logo" width="80" height="80">
  <h1>Afk System</h1>
  <p align="center">
   AFKSystem is a lightweight and customizable plugin designed to manage and handle players who are AFK (Away From
Keyboard)
on your server. With various configurable options, it ensures a smooth gaming experience while addressing AFK-related
issues effectively.
  </p>
</div>


---

## Feature

- **AFK Timer:** `Automatically detects` players who are AFK after a configurable duration.
- **Floating Text:** Displays a `custom tag` above the player’s head when they are AFK, including the reason and
  duration.
- **Broadcast Messages:** Notifies other players when someone goes AFK or returns from being AFK.
- **Auto-Kick:** Optionally removes players who are AFK for too long.
- **Custom Notifications:** Supports `multiple notification` types such as titles, tips, action bars, and popups.
- **Reason Limitation:** Allows players to provide a reason for being AFK with `character limits` to prevent abuse.
- **Form UI Support:** Includes an intuitive `UI Form` for setting AFK reasons.
- **History** add player afk history to `history.log` file

---

## Commands & Permission

| Command            | Argument        | Permission            |
|--------------------|-----------------|-----------------------|
| /afk or /afksystem | <string;reason> | afksystem.command.afk |

Example: /afk 'have lunch'

**Note:** If no argument is provided, the plugin will automatically open the UI Form for the player to set their AFK
reason.

---

## Requirements

- **Commando:** [Download](https://github.com/ACM-PocketMine-MP/Commando/tree/PM5) this version
- **Forms UI** [Download](https://github.com/Vecnavium/FormsUI) this version

---

## Support

If you encounter any errors or have suggestions, please feel free to submit an [Issues](https://github.com/Jasson44/AfkSystem/issues). We’re
happy to assist and improve the plugin based on your feedback!

---

## Update Information

- [1.5.0](changelog/1.5.0.MD)
## Licence

This project is licensed under the `MIT License` - see the [LICENSE](LICENSE) file for details.

---

## TODO
If you have any feature suggestions, open issues
- [X] History player
- [X] Discord Webhook Support
- [ ] Leaderboard ?
