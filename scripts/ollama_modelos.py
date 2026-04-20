import urllib.request, json

try:
    with urllib.request.urlopen("http://127.0.0.1:11434/api/tags") as r:
        data = json.load(r)
    models = data.get("models", [])
    if not models:
        print("Nenhum modelo instalado.")
    else:
        print(f"\n{'Modelo':<30} {'Tamanho':>10}  {'Familia'}")
        print("-" * 55)
        for m in models:
            nome = m["name"]
            gb   = round(m["size"] / 1024**3, 1)
            fam  = m["details"].get("family", "-")
            print(f"  {nome:<28} {gb:>7.1f} GB  {fam}")
    print()
except Exception as e:
    print(f"Erro: {e}\nOllama está rodando?")
