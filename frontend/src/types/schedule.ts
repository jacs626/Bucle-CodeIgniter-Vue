export type ScheduleType = 'fixed' | 'interval' | 'weekly';

const idsMatch = (a: unknown, b: unknown): boolean => String(a ?? '') === String(b ?? '')

export interface Schedule {
  type: ScheduleType;
  time?: string;
  intervalHours?: number;
  daysOfWeek?: string[];
  date?: string;
  startDate?: string;
}

export interface TimeRemaining {
  nextExecution: Date | null;
  days: number;
  hours: number;
  minutes: number;
}

export interface RecurrenceStatus {
  expected: number;
  completed: number;
  pending: number;
  isUpToDate: boolean;
}

export type BlockExecutionStatus = 'pending' | 'done' | 'missed';

const DAY_MAP: Record<string, number> = {
  sunday: 0,
  monday: 1,
  tuesday: 2,
  wednesday: 3,
  thursday: 4,
  friday: 5,
  saturday: 6,
};

function parseTime(time: string): { hours: number; minutes: number } {
  const [h, m] = time.split(':').map(Number);
  return { hours: h || 0, minutes: m || 0 };
}

export function toDate(date: string | Date | undefined): Date | null {
  if (!date) return null;
  if (date instanceof Date) return date;
  return new Date(date);
}

export function formatTimeRemaining(days: number, hours: number, minutes: number): string {
  const parts: string[] = [];
  if (days > 0) parts.push(`${days}d`);
  if (hours > 0) parts.push(`${hours}h`);
  if (minutes > 0 || parts.length === 0) parts.push(`${minutes}m`);
  return parts.join(' ');
}

function isSameDay(date1: Date, date2: Date): boolean {
  return (
    date1.getFullYear() === date2.getFullYear() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getDate() === date2.getDate()
  );
}

export function getTimeUntilNextExecution(
  schedule: Schedule | null | undefined,
  nowParam: Date = new Date()
): TimeRemaining | null {
  const now = nowParam;
  if (!schedule || !schedule.type) return null;

  switch (schedule.type) {
    case 'fixed': {
      const date = toDate(schedule.date);
      if (!date) return null;
      if (date <= now) return null;
      const diff = date.getTime() - now.getTime();
      return {
        nextExecution: date,
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
      };
    }
    case 'interval': {
      if (!schedule.intervalHours || schedule.intervalHours <= 0) return null;
      const intervalMs = schedule.intervalHours * 60 * 60 * 1000;
      const startDate = toDate(schedule.startDate) || now;

      if (startDate > now) {
        const diff = startDate.getTime() - now.getTime();
        return {
          nextExecution: startDate,
          days: Math.floor(diff / (1000 * 60 * 60 * 24)),
          hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
          minutes: Math.floor((diff % (1000 * 60 * 60) / (1000 * 60))),
        };
      }

      const elapsed = now.getTime() - startDate.getTime();
      const nextTime = startDate.getTime() + Math.ceil(elapsed / intervalMs) * intervalMs;
      const nextExecution = new Date(nextTime);

      const diff = nextExecution.getTime() - now.getTime();
      return {
        nextExecution,
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60) / (1000 * 60))),
      };
    }
    case 'weekly': {
      if (!schedule.daysOfWeek?.length || !schedule.time) return null;
      const validDays = schedule.daysOfWeek
        .map(d => DAY_MAP[d.toLowerCase()] ?? 0)
        .filter(d => d !== undefined);
      if (validDays.length === 0) return null;

      validDays.sort((a, b) => a - b);
      const { hours, minutes } = parseTime(schedule.time);
      const nowDay = now.getDay();
      const nowHours = now.getHours();
      const nowMinutes = now.getMinutes();
      const currentTimeMinutes = nowHours * 60 + nowMinutes;
      const targetTimeMinutes = hours * 60 + minutes;

      let nextDay = -1;
      let daysUntil = 0;

      for (let i = 0; i < validDays.length; i++) {
        const day = validDays[i]!;
        if (day > nowDay) {
          nextDay = day;
          daysUntil = day - nowDay;
          break;
        }
        if (day === nowDay && targetTimeMinutes > currentTimeMinutes) {
          nextDay = day;
          daysUntil = 0;
          break;
        }
      }

      if (nextDay === -1) {
        nextDay = validDays[0]!;
        daysUntil = 7 - nowDay + nextDay;
      }

      const nextExecution = new Date(now);
      nextExecution.setDate(now.getDate() + daysUntil);
      nextExecution.setHours(hours, minutes, 0, 0);

      const diff = nextExecution.getTime() - now.getTime();
      return {
        nextExecution,
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60) / (1000 * 60))),
      };
    }
    default:
      return null;
  }
}

export function getExpectedExecutions(
  schedule: Schedule | null | undefined,
  now: Date = new Date()
): number {
  if (!schedule || !schedule.type) return 0;

  const startDate = toDate(schedule.startDate) || now;
  const startTime = startDate.getTime();
  const nowTime = now.getTime();

  if (nowTime < startTime) return 0;

  switch (schedule.type) {
    case 'fixed': {
      const date = toDate(schedule.date);
      if (!date) return 0;
      return date <= now ? 1 : 0;
    }
    case 'interval': {
      if (!schedule.intervalHours || schedule.intervalHours <= 0) return 0;
      const intervalMs = schedule.intervalHours * 60 * 60 * 1000;
      return Math.floor((nowTime - startTime) / intervalMs) + 1;
    }
    case 'weekly': {
      if (!schedule.daysOfWeek?.length || !schedule.time) return 0;
      const validDays = schedule.daysOfWeek
        .map(d => DAY_MAP[d.toLowerCase()] ?? 0)
        .filter(d => d !== undefined);
      if (validDays.length === 0) return 0;

      const { hours, minutes } = parseTime(schedule.time);
      const targetTimeMinutes = hours * 60 + minutes;

      let count = 0;
      const iterDate = new Date(startDate);
      iterDate.setHours(hours, minutes, 0, 0);

      while (iterDate <= now) {
        if (validDays.includes(iterDate.getDay())) {
          if (iterDate.getHours() * 60 + iterDate.getMinutes() <= targetTimeMinutes) {
            count++;
          }
        }
        iterDate.setDate(iterDate.getDate() + 1);
      }
      return count;
    }
    default:
      return 0;
  }
}

export function getCompletedCount(
  historyRecords: { block_id: string | number | null; status: string | null; date: string | null }[],
  blockId: string | number
): number {
  return historyRecords
    .filter(h => idsMatch(h.block_id, blockId) && h.status === 'done')
    .length;
}

export function getRecurrenceStatus(
  schedule: Schedule | null | undefined,
  historyRecords: { block_id: string | number | null; status: string | null; date: string | null }[],
  blockId: string | number,
  blockStatus: string = 'pending',
  now: Date = new Date()
): RecurrenceStatus {
  if (!schedule) {
    return {
      expected: 0,
      completed: blockStatus === 'done' ? 1 : 0,
      pending: blockStatus === 'done' ? 0 : 1,
      isUpToDate: blockStatus === 'done',
    };
  }

  const expected = getExpectedExecutions(schedule, now);
  const completed = getCompletedCount(historyRecords, blockId);
  const pending = Math.max(0, expected - completed);

  return {
    expected,
    completed,
    pending,
    isUpToDate: pending === 0,
  };
}

export function getBlockStatus(
  schedule: Schedule | null | undefined,
  historyRecords: { block_id: string | number | null; status: string | null; date: string | null }[],
  blockId: string | number,
  blockStatus: string = 'pending',
  now: Date = new Date()
): BlockExecutionStatus {
  if (!schedule) {
    return blockStatus === 'done' ? 'done' : 'pending';
  }

  const pastExecutions: Date[] = [];
  const startDate = toDate(schedule.startDate) || new Date(0);

  switch (schedule.type) {
    case 'fixed': {
      const date = toDate(schedule.date);
      if (date && date <= now) {
        pastExecutions.push(date);
      }
      break;
    }
    case 'interval': {
      if (!schedule.intervalHours || schedule.intervalHours <= 0) break;
      const intervalMs = schedule.intervalHours * 60 * 60 * 1000;
      let currentTime = startDate.getTime();
      while (currentTime <= now.getTime()) {
        if (currentTime >= startDate.getTime()) {
          pastExecutions.push(new Date(currentTime));
        }
        currentTime += intervalMs;
      }
      break;
    }
    case 'weekly': {
      if (!schedule.daysOfWeek?.length || !schedule.time) break;
      const validDays = schedule.daysOfWeek
        .map(d => DAY_MAP[d.toLowerCase()] ?? 0)
        .filter(d => d !== undefined);
      if (validDays.length === 0) break;

      const { hours, minutes } = parseTime(schedule.time);
      const iterDate = new Date(now);
      iterDate.setDate(now.getDate() - 7);
      iterDate.setHours(hours, minutes, 0, 0);

      while (iterDate <= now) {
        if (validDays.includes(iterDate.getDay())) {
          if (iterDate >= startDate) {
            pastExecutions.push(new Date(iterDate));
          }
        }
        iterDate.setDate(iterDate.getDate() + 1);
      }
      break;
    }
  }

  if (pastExecutions.length === 0) {
    if (schedule.type === 'fixed' && blockStatus === 'done') {
      return 'done';
    }
    return 'pending';
  }

  const lastExecution = pastExecutions[pastExecutions.length - 1];

  const missedRecord = historyRecords.find(
    h => idsMatch(h.block_id, blockId) && h.status === 'missed' && h.date && isSameDay(new Date(h.date), lastExecution!)
  );
  if (missedRecord) return 'missed';

  if (schedule.type === 'fixed') {
    const doneRecord = historyRecords.find(
      h => idsMatch(h.block_id, blockId) && h.status === 'done' && h.date && isSameDay(new Date(h.date), lastExecution!)
    );
    if (doneRecord || blockStatus === 'done') return 'done';
  }

  return 'pending';
}