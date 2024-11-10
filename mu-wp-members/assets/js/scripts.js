document.addEventListener('DOMContentLoaded', function () {
  var flashMessages = document.querySelectorAll('.flash-message');
  flashMessages.forEach(function (message) {
    setTimeout(function () {
      message.style.display = 'none';
    }, 5000); // 5秒後にメッセージを非表示にする
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const flashMessages = document.querySelectorAll('.flash-message');
  flashMessages.forEach(function (message) {
    message.addEventListener('click', function () {
      this.style.display = 'none';
    });
  });
});
