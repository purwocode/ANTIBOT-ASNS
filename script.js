// Simulasi Login
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
  
    // Simulasi login sederhana
    if (username === "admin" && password === "admin") {
      window.location.href = "index.html"; // Redirect ke dashboard
    } else {
      alert("Username atau password salah!");
    }
  });
  
  // Logout
  document.getElementById('logout').addEventListener('click', function (e) {
    e.preventDefault();
    window.location.href = "login.html"; // Redirect ke halaman login
  });
  
  let shortUrls = []; // Menyimpan data link asli dan hasil short link
  let ispStatus = 'Checking...';
  let blockedIsps = []; // Daftar ISP yang diblokir
  
  // Fungsi untuk menghasilkan short link (contoh sederhana)
  function generateShortLink(originalUrl) {
    return `https://short.url/${Math.random().toString(36).substring(2, 8)}`;
  }
  
  // Fungsi untuk menambahkan short URL
  function addShortUrl(originalUrl) {
    const shortUrl = {
      id: Date.now(), // ID unik
      originalUrl: originalUrl,
      shortUrl: generateShortLink(originalUrl), // Hasil short link
    };
    shortUrls.push(shortUrl);
    updateShortList();
    document.getElementById('shortCount').textContent = shortUrls.length;
  }
  
  // Fungsi untuk mengupdate daftar link asli dan hasil short link
  function updateShortList() {
    const originalLinksList = document.getElementById('originalLinksList');
    const shortenedLinksList = document.getElementById('shortenedLinksList');
  
    // Kosongkan list sebelum diupdate
    originalLinksList.innerHTML = '';
    shortenedLinksList.innerHTML = '';
  
    // Tambahkan link asli dan hasil short link ke dalam list
    shortUrls.forEach((item) => {
      // List Link Asli
      const originalListItem = document.createElement('li');
      originalListItem.className = 'list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn';
      originalListItem.innerHTML = `
        <span>${item.originalUrl}</span>
        <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}" data-type="original">Edit</button>
      `;
      originalLinksList.appendChild(originalListItem);
  
      // List Hasil Short Link
      const shortenedListItem = document.createElement('li');
      shortenedListItem.className = 'list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn';
      shortenedListItem.innerHTML = `
        <span>${item.shortUrl}</span>
        <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}" data-type="shortened">Edit</button>
      `;
      shortenedLinksList.appendChild(shortenedListItem);
    });
  
    // Tambahkan event listener untuk tombol edit
    document.querySelectorAll('.edit-btn').forEach((button) => {
      button.addEventListener('click', (e) => {
        const id = e.target.getAttribute('data-id');
        const type = e.target.getAttribute('data-type'); // 'original' atau 'shortened'
        editLink(id, type);
      });
    });
  }
  
  // Fungsi untuk mengedit link (baik link asli maupun hasil short link)
  function editLink(id, type) {
    const newValue = prompt(`Masukkan ${type === 'original' ? 'URL baru' : 'short link baru'}:`);
    if (newValue) {
      shortUrls = shortUrls.map((item) => {
        if (item.id == id) {
          if (type === 'original') {
            return { ...item, originalUrl: newValue };
          } else if (type === 'shortened') {
            return { ...item, shortUrl: newValue };
          }
        }
        return item;
      });
      updateShortList();
    }
  }
  
  // Event listener untuk form Create Short
  document.getElementById('createShortForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const urlInput = document.getElementById('urlInput').value;
    if (urlInput) {
      addShortUrl(urlInput);
      document.getElementById('urlInput').value = ''; // Reset input
    }
  });
  
  // Fungsi untuk mengupdate status ISP
  function updateIspStatus() {
    const ispStatusElement = document.getElementById('ispStatus');
    ispStatusElement.textContent = ispStatus;
    ispStatusElement.style.color = ispStatus === 'Blocked' ? 'red' : 'green';
  }
  
  // Refresh ISP Status
  document.getElementById('refreshIsp').addEventListener('click', () => {
    ispStatus = ['Blocked', 'Unblocked', 'Checking...'][Math.floor(Math.random() * 3)];
    updateIspStatus();
  });
  
  // Fungsi untuk live update status ISP
  function autoUpdateIspStatus() {
    setInterval(() => {
      ispStatus = ['Blocked', 'Unblocked', 'Checking...'][Math.floor(Math.random() * 3)];
      updateIspStatus();
    }, 5000); // Update setiap 5 detik
  }
  
  // Jalankan fungsi live update
  autoUpdateIspStatus();
  
  // Manual ISP Block
  document.getElementById('manualIspForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const ispName = document.getElementById('ispName').value;
    const action = document.getElementById('ispAction').value;
  
    if (ispName) {
      if (action === 'block') {
        if (!blockedIsps.includes(ispName)) {
          blockedIsps.push(ispName);
          ispStatus = 'Blocked';
          alert(`ISP "${ispName}" telah diblokir.`);
        } else {
          alert(`ISP "${ispName}" sudah diblokir.`);
        }
      } else if (action === 'unblock') {
        if (blockedIsps.includes(ispName)) {
          blockedIsps = blockedIsps.filter((isp) => isp !== ispName);
          ispStatus = 'Unblocked';
          alert(`ISP "${ispName}" telah dibuka blokir.`);
        } else {
          alert(`ISP "${ispName}" tidak ditemukan dalam daftar blokir.`);
        }
      }
      updateIspStatus();
      updateBlockedIspsList(); // Update daftar ISP yang diblokir
    } else {
      alert('Silakan masukkan nama ISP.');
    }
  });
  
  // Fungsi untuk mengupdate daftar ISP yang diblokir
  function updateBlockedIspsList() {
    const blockedIspsList = document.getElementById('blockedIspsList');
    blockedIspsList.innerHTML = ''; // Kosongkan list sebelum diupdate
  
    blockedIsps.forEach((isp) => {
      const listItem = document.createElement('li');
      listItem.className = 'list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn';
      listItem.textContent = isp;
      blockedIspsList.appendChild(listItem);
    });
  }