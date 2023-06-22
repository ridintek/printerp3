
export class IncomeStatement {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('IncomeStatement::table() Cannot find tbody.');
    }

    return this;
  }

  static addRow(row, className = '') {
    if (isObject(row)) {
      this.tbody.append(`<tr><td class="${className}">${row.key}</td><td class="text-right ${className}">${formatCurrency(row.value, 'Rp')}</td></tr>`);
    }
  }

  static clean() {
    this.tbody.empty();

    return this;
  }

  static load(opt = {}) {
    let param = '';

    this.tbody.html(`<tr class="odd"><td colspan="2" class="dataTables_empty">Loading data from server...</td></tr>`);

    if (opt.biller) {
      for (let b of opt.biller) {
        param += '&biller[]=' + b;
      }
    }

    if (opt.start_date) {
      param += '&start_date=' + opt.start_date;
    }

    if (opt.end_date) {
      param += '&end_date=' + opt.end_date;
    }

    if (param.charAt(0) == '&') {
      param = param.slice(1);
    }

    $.ajax({
      method: 'GET',
      success: (response) => {
        if (isObject(response)) {
          this.clean();

          for (let row of response.data) {
            if (row.name) {
              if (!isArray(row.data)) {
                this.addRow({
                  key: row.name,
                  value: row.amount
                }, 'text-bold');
              } else if (isArray(row.data)) {
                this.addRow({
                  key: row.name,
                  value: row.amount
                }, 'text-bold');

                for (let subRow of row.data) {
                  this.addRow({
                    key: '--> ' + subRow.name,
                    value: subRow.amount
                  });
                }
              }
            }
          }
        }
      },
      url: base_url + '/report/getIncomeStatements?' + param
    });
  }

  reload() {
    this.clean();
    this.load();
  }
}

export class InternalUse {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('InternalUse::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item, allowDuplicate = false) {
    if (!this.tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this.tbody.find('.item-id');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    let option = `<option value="">${lang.App.allmachine}</option>`;

    item.hash = randomString();

    if (erp.machine && isArray(erp.machine)) {
      erp.machine.forEach((machine) => {
        option += `<option value="${machine.id}">${machine.name}</option>`;
      });
    }

    this.tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
        <input type="hidden" name="item[code][]" value="${item.code}">
        <input type="hidden" name="item[unique][]" value="${item.unique ?? ''}">
        <td>(${item.code}) ${item.name}</td>
        <td>
          <div class="card card-dark card-tabs">
            <div class="card-header bg-gradient-dark p-0 pt-1">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <a href="#tab-machine-${item.hash}" class="nav-link active" data-toggle="pill">${lang.App.machine}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-counter-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.counter}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-ucr-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.ucr}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-quantity-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.quantity}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade active show" id="tab-machine-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.machine}</label>
                    <select id="item-machine-${item.hash}" name="item[machine][]" class="select" data-placeholder="${lang.App.machine}" style="width:100%">
                      ${option}
                    </select>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-counter-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.counter}</label>
                    <input name="item[counter][]" class="form-control form-control-border form-control-sm" placeholder="${lang.App.counter}" value="${item.counter ?? ''}">
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-ucr-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.uniquecodereplacement}</label>
                    <input name="item[ucr][]" class="form-control form-control-border form-control-sm" placeholder="${lang.App.uniquecodereplacement}" value="${item.ucr ?? ''}">
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-quantity-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.quantity}</label>
                    <input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td>${item.unit}</td>
        <td>${formatNumber(item.current_qty)}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);

    if (item.machine) {
      preSelect2('product', `#item-machine-${item.hash}`, item.machine).catch(err => console.warn(err));
    }
  }
}

export class Notification {
  static reload() {

  }
}

export default class Ridintek {
  tbody = null;

  constructor(table) {
    this.tbody = $(table).find('tbody');
  }

  addItem(item) {
    console.log(item);
  }
}

export class ProductMutation {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('ProductMutation::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item, allowDuplicate = false) {
    if (!this.tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this.tbody.find('.item-id');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    item.hash = randomString();

    this.tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
        <input type="hidden" name="item[code][]" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td>
          <div class="card card-dark card-tabs">
            <div class="card-header bg-gradient-dark p-0 pt-1">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <a href="#tab-quantity-${item.hash}" class="nav-link active" data-toggle="pill">${lang.App.quantity}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade active show" id="tab-quantity-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.quantity}</label>
                    <input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td>${item.unit}</td>
        <td>${formatNumber(item.current_qty)}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);
  }
}

export class ProductPurchase {
  static _tbody = null;
  static _mode = 'add';

  static table(table) {
    this._tbody = $(table).find('tbody');

    if (!this._tbody.length) {
      console.log('ProductPurchase::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item, allowDuplicate = false) {
    if (!this._tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this._tbody.find('.item-id');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    item.hash = randomString();
    item.rest_qty = (item.quantity - item.received_qty);

    console.log(item);

    this._tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
        <input type="hidden" name="item[code][]" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td>
          <div class="card card-dark card-tabs">
            <div class="card-header bg-gradient-dark p-0 pt-1">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <a href="#tab-quantity-${item.hash}" class="nav-link active" data-toggle="pill">${lang.App.quantity}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-spec-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.spec}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-cost-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.cost}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade active show" id="tab-quantity-${item.hash}">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>${lang.App.quantity}</label>
                        <input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>${lang.Status.received}</label>
                        <input type="number" name="item[received][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.received_qty ?? 0)}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>${lang.App.restqty}</label>
                        <input type="number" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.rest_qty ?? 0)}" readonly>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-spec-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.spec}</label>
                    <input name="item[spec][]" class="form-control form-control-border form-control-sm" value="${item.spec}">
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-cost-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.cost}</label>
                    <input name="item[cost][]" class="form-control form-control-border form-control-sm currency" value="${formatCurrency(item.cost)}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td>${item.unit}</td>
        <td>${formatNumber(item.current_qty)}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);
  }

  static mode(mode) {
    this._mode = mode;

    return this;
  }
}

export class ProductTransfer {
  static _tbody = null;
  static _mode = 'add';

  static table(table) {
    this._tbody = $(table).find('tbody');

    if (!this._tbody.length) {
      console.log('ProductTransfer::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item, allowDuplicate = false) {
    if (!this._tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this._tbody.find('.item-id');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    item.hash = randomString();
    item.rest = (item.quantity - item.received_qty);

    console.log(item);

    this._tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
        <input type="hidden" name="item[code][]" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td>
          <div class="card card-dark card-tabs">
            <div class="card-header bg-gradient-dark p-0 pt-1">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <a href="#tab-quantity-${item.hash}" class="nav-link active" data-toggle="pill">${lang.App.quantity}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-spec-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.spec}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-markon_price-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.markonprice}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade active show" id="tab-quantity-${item.hash}">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>${lang.App.quantity}</label>
                        <input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>${lang.Status.received}</label>
                        <input type="number" name="item[received][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.received_qty ?? 0)}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>${lang.App.restqty}</label>
                        <input type="number" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.rest_qty ?? 0)}" readonly>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-spec-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.spec}</label>
                    <input name="item[spec][]" class="form-control form-control-border form-control-sm" value="${item.spec}">
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-markon_price-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.markonprice}</label>
                    <input name="item[markon_price][]" class="form-control form-control-border form-control-sm currency" value="${formatCurrency(item.markon_price)}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td>${item.unit}</td>
        <td>${formatNumber(item.current_qty)}</td>
        <td>${formatNumber(item.destination_qty)}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);
  }

  static mode(mode) {
    this._mode = mode;

    return this;
  }
}

/**
 * QueueConfig
 */
export class QueueConfig {
  static clear() {
    sessionStorage.clear();
  }

  static delete(name) {
    return sessionStorage.removeItem(name);
  }

  static get(name) {
    return sessionStorage.getItem(name);
  }

  static getObject(name) {
    return JSON.parse(sessionStorage.getItem(name));
  }

  static set(name, value) {
    let val = value;
    if (typeof value === 'object') val = JSON.stringify(value);
    sessionStorage.setItem(name, val);
  }

  static setObject(name, value) {
    this.set(name, value);
  }
}

/**
* QueueHttp
*/
export class QueueHttp {
  constructor() {
    this._headers = {};
  }

  static setHeaders(headers = {}) {
    this._headers = headers;
    return this;
  }

  static async send(method, url, data = null) {
    return new Promise((resolve, reject) => {
      $.ajax({
        data: data,
        error: (xhr) => {
          console.log(xhr);
          reject(xhr.responseJSON);
        },
        headers: this._headers,
        method: method,
        success: (data) => {
          resolve(data);
        },
        url: url
      });
    });
  }
}

/**
 * QueueManagementSystem
 */
export class QMS {
  static async addQueueTicket(data) {
    return new Promise((resolve, reject) => {
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/addQueueTicket', data));
    });
  }

  static async callQueueTicket(warehouseId) {
    return new Promise((resolve, reject) => {
      let data = {};
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/callQueueTicket/' + warehouseId, data));
    });
  }

  static async endQueueTicket(data) {
    return new Promise((resolve, reject) => {
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/endQueueTicket', data));
    });
  }

  static async getDisplayData(warehouseId) {
    return new Promise((resolve, reject) => {
      resolve(QueueHttp.send('GET', base_url + '/qms/getDisplayData/' + warehouseId));
    });
  }

  static async recallQueueTicket(ticketId) {
    return new Promise((resolve, reject) => {
      let data = {};
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/recallQueueTicket/' + ticketId, data));
    });
  }

  static async sendDisplayResponse(ticketId) {
    return new Promise((resolve, reject) => {
      let data = {};
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/displayResponse/' + ticketId, data));
    });
  }

  static async sendReport(data = {}) {
    return new Promise((resolve, reject) => {
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/sendReport', data));
    });
  }

  static async serveQueueTicket(ticketId) {
    return new Promise((resolve, reject) => {
      let data = {};
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/serveQueueTicket/' + ticketId, data));
    });
  }

  static async setCounter(counter) {
    return new Promise((resolve, reject) => {
      let data = {};
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/setCounter/' + counter, data));
    });
  }

  static async skipQueueTicket(data) {
    return new Promise((resolve, reject) => {
      data.__ = __;
      resolve(QueueHttp.send('POST', base_url + '/qms/skipQueueTicket', data));
    });
  }
}

export class QueueNotify {
  static audio;

  static {
    this.audio = {
      error: new Audio(`${base_url}/assets/qms/audio/nasty-error-short.mp3`),
      success: new Audio(`${base_url}/assets/qms/audio/when.mp3`),
      warning: new Audio(`${base_url}/assets/qms/audio/system-fault.mp3`)
    };
  }

  static error(msg, delay = 5) {
    toastr.options.timeOut = delay * 1000;
    toastr.error(msg);
    this.audio.error.play();
  }

  static success(msg, delay = 5) {
    toastr.options.timeOut = delay * 1000;
    toastr.success(msg);
    this.audio.success.play();
  }

  static warning(msg, delay = 5) {
    toastr.options.timeOut = delay * 1000;
    toastr.warning(msg);
    this.audio.warning.play();
  }
}

/**
 * QueueTimer
 */
export class QueueTimer {
  static CLOCKWISE_MODE = 0;
  static COUNTERCLOCKWISE_MODE = 1;

  constructor(selector = null) {
    this._mode = 0;
    this._cb = []; // array of [event: '', callback: null]
    this._hElm = null;
    this._hTimer = null;
    this._limit = {
      hours: '', minutes: '', seconds: ''
    };
    this._sec = 0;

    if (selector) {
      this._hElm = document.querySelector(selector);

      if (!this._hElm) {
        console.warn('QueueTimer::constructor(): Element is not defined.');
      }
    }
  }

  decrement(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec -= (hours * 3600) + (minutes * 60) + seconds;

    for (let a in this._cb) {
      if (this._cb[a].event == 'decrement' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    return this;
  }

  increment(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec += (hours * 3600) + (minutes * 60) + seconds;

    for (let a in this._cb) {
      if (this._cb[a].event == 'increment' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    return this;
  }

  getHours() {
    let hour = Math.floor(this._sec / 3600);
    hour = (hour < 10 ? '0' + hour : hour);
    return hour;
  }

  getMinutes() {
    let min = Math.floor((this._sec % 3600) / 60);
    min = (min < 10 ? '0' + min : min);
    return min;
  }

  getSeconds() {
    let sec = Math.floor((this._sec % 3600) % 60);
    sec = (sec < 10 ? '0' + sec : sec);
    return sec;
  }

  getTime() {
    return `${this.getHours()}:${this.getMinutes()}:${this.getSeconds()}`;
  }

  isRunning() {
    return (this._hTimer ? true : false);
  }

  /**
   * Event callback for `limit`, `reset`, `start`, `set`, `stop`, `ticking`, `timeout`.
   *
   * - `limit` Reached after timer equal as `setLimit` time.
   * - `reset` Reached after reset event occurred.
   * - `start` Reached after start event occurred.
   * - `set` Reached after set event occurred.
   * - `stop` Reached after stop event occurred.
   * - `ticking` Reached every second if timer has been started.
   * - `timeout` Reached after timer become `00:00:00`.
   *
   * @param {string} event
   * @param {function} callback
   * @returns `QueueTimer`
   */
  on(event, callback) {
    this._cb.push({
      event: event,
      callback: callback
    });

    return this;
  }

  /**
   * Reset timer into `00:00:00`.
   * @returns `QueueTimer`
   */
  reset() {
    this._sec = 0;

    for (let a in this._cb) {
      if (this._cb[a].event == 'reset' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    if (this._hElm) {
      this._hElm.innerHTML = this.getTime();
    }

    return this;
  }

  seconds() {
    return this._sec;
  }

  set(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec = (hours * 3600) + (minutes * 60) + seconds;

    if (this._hElm) {
      this._hElm.innerHTML = time_str;
    }

    for (let a in this._cb) {
      if (this._cb[a].event == 'set' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    return this;
  }

  /**
   * Set timer limit if time is reached. Suitable for increment or decrement.
   * @param {string} time_str Time string 'hh:mm:ss'
   * @returns Return QueueTimer object.
   */
  setLimit(time_str) {
    this._limit.hours = time_str.split(':')[0];
    this._limit.minutes = time_str.split(':')[1];
    this._limit.seconds = time_str.split(':')[2];

    return this;
  }

  /**
   * Set Timer mode.
   *
   * Available mode:
   * - `QueueTimer.CLOCKWISE_MODE` Timer will count up.
   * - `QueueTimer.COUNTERCLOCKWISE_MODE` Timer will count down.
   *
   * @param {Number} mode
   * @returns
   */
  setMode(mode) {
    this._mode = mode;

    return this;
  }

  setMiliseconds(miliseconds) {
    this._sec = Math.floor(miliseconds / 1000);

    return this;
  }

  setSeconds(seconds) {
    this._sec = seconds;

    return this;
  }

  start() {
    if (this._hTimer) {
      console.warn('hTimer instance is invalid.');
      return false; // If instance present, then ignore it.
    }

    // Callback Handler.
    for (let a in this._cb) {
      if (this._cb[a].event == 'start' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    this._hTimer = setInterval(() => {
      // Increment timer.
      if (this._mode == QueueTimer.CLOCKWISE_MODE) {
        this._sec++;
      }

      // Decrement timer.
      if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) {
        // Prevent minus decrement.
        if (!(this.getHours() == '00' && this.getMinutes() == '00' && this.getSeconds() == '00')) {
          this._sec--;
        }
      }

      // Callback Handler.
      for (let a in this._cb) {
        if (this._cb[a].event == 'limit' && typeof this._cb[a].callback == 'function') {
          if (this.getHours() == this._limit.hours && this.getMinutes() == this._limit.minutes && this.getSeconds() == this._limit.seconds) {
            this._cb[a].callback.call(this, this);
            this.stop(); // Stop after limit reached.
          }
        }

        if (this._cb[a].event == 'ticking' && typeof this._cb[a].callback == 'function') {
          this._cb[a].callback.call(this, this);
        }

        if (this._cb[a].event == 'timeout' && typeof this._cb[a].callback == 'function') {
          if (this.getHours() == '00' && this.getMinutes() == '00' && this.getSeconds() == '00') {
            if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) {
              this._cb[a].callback.call(this, this);
            }
            this.stop(); // Stop after timeout reached.
          }
        }
      }

      // WRONG POSITION.
      // if (this._mode == QueueTimer.CLOCKWISE_MODE) this._sec++;
      // if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) this._sec--;

      if (this._hElm) {
        this._hElm.innerHTML = `${this.getHours()}:${this.getMinutes()}:${this.getSeconds()}`;
      }
    }, 1000);
    return true;
  }

  stop() {
    if (this._hTimer) {
      window.clearInterval(this._hTimer);

      // Callback Handler.
      for (let a in this._cb) {
        if (this._cb[a].event == 'stop' && typeof this._cb[a].callback == 'function') {
          this._cb[a].callback.call(this, this);
        }
      }

      this._hTimer = null;
    }

    return this;
  }
}

export class ReportExport {
  static _cb = [];

  static bind(action, selector) {
    if (action == 'click') {
      $(document).on('click', selector, (ev) => {
        for (let a in this._cb) {
          if (this._cb[a].ev == 'click' && typeof this._cb[a].cb == 'function') {
            this._cb[a].cb.call(this, this);
          }
        }

        $.ajax({

        });
      });
    }

    return this;
  }

  static on(event, callback) {
    this._cb.push({
      ev: event,
      cb: callback
    });

    return this;
  }
}

export class Sale {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('Sale::table() Cannot find tbody.');
    }

    return this;
  }

  static clear() {
    this.tbody.empty();

    calculateSale();
  }

  static addItem(item, allowDuplicate = false) {
    if (!this.tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this.tbody.find('.item-id');

      for (let i of items) {
        if (item.id == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    item.hash = randomString();
    item.area = item.width * item.length;
    item.price = getSalePrice(item.area * item.quantity, item.ranges, item.prices);
    item.subtotal = item.area * item.price * item.quantity;

    let readOnly = (item.category != 'DPI' ? ' readonly' : '');
    let priceReadOnly = (hasAccess('Sale.EditPrice') ? '' : ' readonly');

    this.tbody.prepend(`
      <tr>
        <td class="col-md-3">
          <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
          <input type="hidden" name="item[code][]" value="${item.code}">
          <input type="hidden" name="item[name][]" value="${item.name}">
          <input type="hidden" name="item[finished_qty][]" value="${item.finished_qty ?? 0}">
          <input type="hidden" name="item[complete][]" value="${htmlEscape(JSON.stringify(item.complete))}">
          <input type="hidden" name="item[completed_at][]" value="${item.completed_at ?? 0}">
          <input type="hidden" name="item[prices][]" value="${JSON.stringify(item.prices)}">
          <input type="hidden" name="item[ranges][]" value="${JSON.stringify(item.ranges)}">
          <input type="hidden" name="item[status][]" value="${item.status ?? ''}">
          <input type="hidden" name="item[type][]" value="${item.type}">
          <input type="hidden" name="item[customprice][]" value="false">
          (${item.code}) ${item.name}
        </td>
        <td>
          <div class="card card-dark card-tabs">
            <div class="card-header bg-gradient-dark p-0 pt-1">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <a href="#tab-size-${item.hash}" class="nav-link active" data-toggle="pill">${lang.App.size}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-spec-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.spec}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-opr-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.operator}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-price-${item.hash}" class="nav-link" data-toggle="pill">${lang.App.price}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade active show" id="tab-size-${item.hash}">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.width}</label>
                        <input name="item[width][]" type="number" class="form-control form-control-border form-control-sm saleitem" min="0" value="${item.width}" style="max-width:60px" ${readOnly}>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.length}</label>
                        <input name="item[length][]" type="number" class="form-control form-control-border form-control-sm saleitem" min="0" value="${item.length}" style="max-width:60px" ${readOnly}>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.area}</label>
                        <input name="item[area][]" type="number" class="form-control form-control-border form-control-sm" min="0" value="${item.area}" style="max-width:60px" readonly>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.quantity}</label>
                        <input name="item[quantity][]" type="number" class="form-control form-control-border form-control-sm saleitem" min="0" value="${item.quantity}" style="max-width:60px">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-spec-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.spec}</label>
                    <input name="item[spec][]" class="form-control form-control-border form-control-sm" placeholder="${lang.App.spec}" value="${item.spec}">
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-opr-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.operator}</label>
                    <select id="item-opr-${item.hash}" name="item[operator][]" class="select-operator" data-placeholder="${lang.App.operator}" style="width:100%">
                      <option value=""></option>
                    </select>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-price-${item.hash}">
                  <div class="form-group">
                    <label>${lang.App.price}</label>
                    <input name="item[price][]" class="form-control form-control-border form-control-sm currency saleitem" value="${item.price}" ${priceReadOnly}>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td><span class="float-right saleitem-subtotal">${formatCurrency(item.subtotal)}</span></td>
        <td><a href="#" class="table-row-delete sale-row"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);

    if (item.operator) {
      preSelect2('user', `#item-opr-${item.hash}`, item.operator).catch(err => console.warn(err));
    }

    calculateSale();
  }
}

export class SaleItem {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('SaleItem::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item) {
    if (!this.tbody.length) {
      return false;
    }

    if (item.status != 'waiting_production' && item.status != 'completed_partial') {
      toastr.error(`Item ${item.product_code} is not in production status.`);
      return false;
    }

    let restQty = (item.quantity - item.finished_qty);

    this.tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" value="${item.id}">
        <input type="hidden" name="item[sale_id][]" value="${item.sale_id}">
        <input type="hidden" name="item[finished_qty][]" value="${item.finished_qty}">
        <input type="hidden" name="item[total_qty][]" value="${item.quantity}">
        <input type="hidden" name="item[code][]" value="${item.product_code}">
        <td>${item.sale}</td>
        <td>(${item.product_code}) ${item.product_name}</td>
        <td><input type="number" class="form-control form-control-border form-control-sm text-center" min="0" value="${filterDecimal(item.quantity)}" readonly></td>
        <td><input type="number" class="form-control form-control-border form-control-sm text-center" min="0" value="${filterDecimal(item.finished_qty)}" readonly></td>
        <td><input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm text-center" min="0" value="${restQty}"></td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);

    return this;
  }

  static addRow(row) {
    this.tbody.prepend(`<tr>${row}</tr>`);

    return this;
  }

  static clear() {
    this.tbody.empty();

    return this;
  }
}

export class StockAdjustment {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('StockAdjustment::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item, allowDuplicate = false) {
    if (!this.tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this.tbody.find('.item-id');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    this.tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
        <input type="hidden" name="item[code][]" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td><input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}"></td>
        <td>${formatNumber(item.current_qty)}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);
  }
}

export class StockOpname {
  static tbody = null;

  static table(table) {
    this.tbody = $(table).find('tbody');

    if (!this.tbody.length) {
      console.log('StockOpname::table() Cannot find tbody.');
    }

    return this;
  }

  static addItem(item, allowDuplicate = false) {
    if (!this.tbody.length) {
      return false;
    }

    if (!allowDuplicate) {
      let items = this.tbody.find('.item-id');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    let delRow = (hasAccess('StockOpname.Edit')
      ? '<a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a>'
      : '');

    this.tbody.prepend(`
      <tr>
        <input type="hidden" name="item[id][]" class="item-id" value="${item.id}">
        <input type="hidden" name="item[code][]" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td>${item.unit}</td>
        <td><input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}"></td>
        <td><input type="number" name="item[reject][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.reject)}"></td>
        <td>${delRow}</td>
      </tr>
    `);
  }

  static addRow(row) {
    this.tbody.prepend(`<tr>${row}</tr>`);

    return this;
  }

  static clear() {
    this.tbody.empty();

    return this;
  }
}

export class TableFilter {
  static _cb = [];

  static bind(action, selector) {
    if (action == 'apply') {
      $(document).on('click', selector, (ev) => {
        for (let a in this._cb) {
          if (this._cb[a].ev == 'apply' && typeof this._cb[a].cb == 'function') {
            this._cb[a].cb.call(this, this);
          }
        }

        if (erp?.table) {
          erp.table.draw(false);
        }

        controlSidebar('collapse');
      });
    }

    if (action == 'clear') {
      $(document).on('click', selector, (ev) => {
        for (let a in this._cb) {
          if (this._cb[a].ev == 'clear' && typeof this._cb[a].cb == 'function') {
            this._cb[a].cb.call(this, this);
          }
        }

        if (erp?.table) {
          erp.table.draw(false);
        }

        controlSidebar('collapse');
      });
    }

    return this;
  }

  static on(event, callback) {
    this._cb.push({
      ev: event,
      cb: callback
    });

    return this;
  }
}