const User = require('./User');
const Note = require('./Note');
const Label = require('./Label');
const { NoteLabel, NoteShare } = require('./Relations');

// User <-> Note
User.hasMany(Note, { foreignKey: 'userId', onDelete: 'CASCADE' });
Note.belongsTo(User, { foreignKey: 'userId', as: 'owner' });

// User <-> Label
User.hasMany(Label, { foreignKey: 'userId', onDelete: 'CASCADE' });
Label.belongsTo(User, { foreignKey: 'userId' });

// Note <-> Label (many-to-many) - NO cascade to avoid SQL Server multi-cascade error
Note.belongsToMany(Label, { through: NoteLabel, foreignKey: 'noteId', as: 'labels', onDelete: 'NO ACTION' });
Label.belongsToMany(Note, { through: NoteLabel, foreignKey: 'labelId', onDelete: 'NO ACTION' });

// Note <-> NoteShare
Note.hasMany(NoteShare, { foreignKey: 'noteId', as: 'shares', onDelete: 'CASCADE' });
NoteShare.belongsTo(Note, { foreignKey: 'noteId' });
NoteShare.belongsTo(User, { foreignKey: 'sharedWithUserId', as: 'sharedWithUser' });
NoteShare.belongsTo(User, { foreignKey: 'sharedByUserId', as: 'sharedByUser' });

module.exports = { User, Note, Label, NoteLabel, NoteShare };
