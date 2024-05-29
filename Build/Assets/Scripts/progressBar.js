export class ProgressBar {
  progressBar
  constructor (progressBar) {
    this.progressbar = progressBar
    this.init()
  }

  init () {
    /* PROGRESS BAR */
    this.counterContainer = this.progressbar.getElementsByClassName('counterContainer')[0]
  }

  /* progress bar reset */
  pbReset () {
    this.counterContainer.querySelector('.counterAmount').style.width = '0%'
    this.counterContainer.classList.remove('progress', 'error', 'success')
    this.counterContainer.querySelector('.errorMessage').innerHTML = 'error'
  }

  /* set status for progress bar */
  setPbStatus (status) {
    this.pbReset()
    this.counterContainer.classList.add(status)
    if ((status === 'success') || (status === 'error')) {
      this.counterContainer.querySelector('.counterAmount').style.width = '100%'
    }
  }

  /* error output for progress bar */
  errorHandling (errorMessage) {
    this.setPbStatus('error')
    this.progressbar.querySelector('.errorMessage').append(': ' + errorMessage.substring(0, 130))
  }
}
