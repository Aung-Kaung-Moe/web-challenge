document.addEventListener("DOMContentLoaded", () => {
  const girls = [
    { name: "Hyuga Hinata", img: "images/hinata.jpg" },
    { name: "Mikasa Ackerman", img: "images/mikasa.jpg" },
    { name: "Boa Hancock", img: "images/boa.jpg" },
    { name: "Nobara Kugisaki", img: "images/nobara.jpg" },
    { name: "Nezuko Kamado", img: "images/nezuko.jpg" }
  ];

  const girl = girls[Math.floor(Math.random() * girls.length)];

  const img = document.getElementById("girlImg");
  const link = document.getElementById("imgLink");
  const btn = document.getElementById("bfBtn");
  const out = document.getElementById("resultBox");

  img.src = girl.img;
  img.alt = girl.name;
  link.href = girl.img;

  btn.textContent = `Click me to be ${girl.name}'s boyfriend`;

  btn.addEventListener("click", async () => {
    const form = new URLSearchParams();
    form.set("boyfriend", "true"); // decoy
    form.set("girl", girl.name);   // for dynamic message

    const res = await fetch("flag.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: form.toString()
    });

    out.textContent = await res.text();
  });
});
