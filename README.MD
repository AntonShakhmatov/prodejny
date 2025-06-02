# API Dokumentace – Stores

API umožňuje správu seznamu prodejen (stores) pro integrace s dalšími systémy, frontendovými aplikacemi apod.

---

## Základní URL

https://{web}/api/shoptet/stores


- **Formát odpovědí:** `application/json`
- **Podporuje CORS:** Ano (vhodné pro JS aplikace, Postman apod.)

---

## Struktura modelu prodejny

| Pole                   | Typ       | Popis                                           |
|------------------------|-----------|--------------------------------------------------|
| `id`                   | int       | ID prodejny                                     |
| `title`                | string    | Název prodejny (**povinné**)                    |
| `region`               | string    | Region                                          |
| `delivery_point_address` | string  | Adresa ve formátu "Ulice, Město"               |
| `shop_url`             | string    | URL prodejny (volitelné)                        |
| `gps_latitude`         | float     | GPS šířka                                       |
| `gps_longitude`        | float     | GPS délka                                       |
| `opening_hours`        | JSON      | Otevírací doba (viz příklad níže)               |
| `description`          | string    | Popis                                           |
| `phone`                | string    | Telefon                                         |
| `email`                | string    | Email                                           |
| `image_url`            | string    | Relativní URL obrázku                           |
| `note`                 | string    | Poznámka                                        |
| `extra_note`           | string    | Mimořádná informace                             |
| `visible`              | boolean/int | Aktivní (1 = ano, 0 = ne)                      |

---

## Příklad `opening_hours`

```json
{
  "0": {"from": "08:00", "to": "18:00", "closed": false},
  "5": {"from": "09:00", "to": "13:00", "closed": false},
  "6": {"from": "Zavřeno", "to": "Zavřeno", "closed": true}
}
0 = Pondělí, ..., 6 = Neděle

![back](images/2025-06-02-15-47-16_fttHSzbI.gif)

Endpointy
GET /stores
Získá seznam všech prodejen.
Možné parametry: např. visible=1

Odpověď:

[
  {
    "id": 5,
    "title": "Kitos Praha",
    "region": "Praha",
    "delivery_point_address": "Obchodní 123, Praha",
    "shop_url": "https://...",
    "gps_latitude": 50.087451,
    "gps_longitude": 14.420671,
    "opening_hours": "{\"0\":{\"from\":\"08:00\",\"to\":\"18:00\",\"closed\":false}}",
    "description": "Výdejní místo e-shopu Kitos",
    "phone": "+420 123 456 789",
    "email": "praha@...",
    "image_url": "/img/store_abc.jpg",
    "note": "Změna otevírací doby v pátek",
    "extra_note": "Mimořádná info",
    "visible": 1
  }
]


GET /stores/{id}
Získá detail konkrétní prodejny.
Vrací 404, pokud neexistuje nebo nesplňuje filtr.

POST /stores
Vytvoří novou prodejnu.

Povinné pole:

title

Doporučené:

delivery_point_address, visible, další dle potřeby

Příklad:

{
  "title": "Kitos Brno",
  "delivery_point_address": "Náměstí 1, Brno",
  "region": "Jihomoravský kraj",
  "gps_latitude": 49.1951,
  "gps_longitude": 16.6068,
  "shop_url": "https://...",
  "opening_hours": "{\"0\":{\"from\":\"09:00\",\"to\":\"17:00\",\"closed\":false}}",
  "description": "Kitos Brno prodejna",
  "email": "brno@...",
  "phone": "+420 321 654 987",
  "image_url": "/img/store_brno.jpg",
  "note": "Zavřeno 5. května",
  "extra_note": "Omezený vstup",
  "visible": 1
}

PUT /stores/{id}
Upraví existující prodejnu (stejná struktura jako POST).

Odpověď:

{ "status": "ok", "id": 123 }


DELETE /stores/{id}
Smaže prodejnu dle ID.

Odpověď:

{ "status": "deleted", "id": 123 }

POST /stores/{id}/upload-image
Nahrání obrázku pro prodejnu.

Formát: multipart/form-data
Parametr: image (soubor)

Odpověď:

{ "status": "ok", "url": "/img/store_xyz.jpg" }


Testovací HTML formulář:
<form enctype="multipart/form-data" method="post" action="/api/shoptet/stores/{id}/upload-image">
  <input type="file" name="image" accept="image/*" required />
  <button type="submit">Upload</button>
</form>

Poznámky
opening_hours je ukládán jako JSON objekt s klíči 0–6 (pondělí–neděle)

Každý den obsahuje:

from: otevírací hodina

to: zavírací hodina

closed: true pokud zavřeno

Příklad zavřeného dne:
{ "5": { "from": "Zavřeno", "to": "Zavřeno", "closed": true } }

!!!Upozornění: Pro operace POST, PUT, DELETE, upload-image je vyžadována autentizace dle nastavení serveru.

![realizace](images/2025-06-02-15-51-45_xG1Pah8P.gif)