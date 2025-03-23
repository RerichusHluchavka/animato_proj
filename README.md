# Animato projek

## Popis
Tento projekt slouží k náhrání produktů do databáze a exportu objednávek z databáze do formátu XML který by měl být kompatibilní s Pohoda.

### Použité technologie
- PHP 8.3
- MariaDb
- Docker

## Spuštění
Pro použití je třeba mít nainstalovaný Docker a Docker-compose. Lze využít: (https://www.docker.com/products/docker-desktop/)

Při prvním spuštění je zapotřebí v domovském adresáří projektu spustit příkaz pro vybuduvání a spuštění dockeru:
```bash
docker-compose up -d --build
```

Pro spuštění aplikace v dalších případech stačí pouze:
```bash
docker-compose up -d
```

Po spuštění dockeru bude aplikace dostupná na adrese: http://localhost:8080

## Poznámky
Oproti zadání se do databáze produktů načítají všechna data a ne jen pouze specifikovaná v zadání, neboť při tvorbě jsem na to lehce zapomněl 😅.

Také jelikož jsem začal dělat projekt až o víkendu a chtěl jsem ho stihnout do pondělí, tak jsem nepsal žadné emaily na dotazy které bych jinak měl. Takže výsledek jsem udělal podle toho jak jsem to pochopil z emailu, jako například exportování dat nebo použití 2 databází (jedna pro produkty a druhá pro objednávky).

