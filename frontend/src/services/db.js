import Dexie from 'dexie';

export const db = new Dexie('NoteAppLocalDB');
db.version(2).stores({
  notes: '++id, title, content, isPinned, pinnedAt, updatedAt, userId',
  syncQueue: '++id, action, endpoint, payload'
});
